<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\RouterInterface;

class Client implements ClientInterface, LoggerAwareInterface
{
	/** @var GuzzleClient|null */
	protected $client;

	/** @var RouterInterface */
	protected $router;

	/** @var array[] */
	protected $credentials;

	/** @var LoggerInterface */
	protected $logger;

	/** @var string */
	private $githubSecret;

	public function __construct(RouterInterface $router, string $githubSecret)
	{
		$this->router       = $router;
		$this->githubSecret = $githubSecret;
		$this->logger       = new NullLogger;
	}

	public function addCredentials(string $username, string $password)
	{
		$this->credentials[] = [$username, $password];
	}

	public function setCredentials(array $credentials)
	{
		$this->credentials = $credentials;
	}

	/** {@inheritdoc} */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/** {@inheritdoc} */
	public function createHook(string $owner, string $repo, array $events): int
	{
		$client = $this->getClient();

		try
		{
			$callbackUrl = $this->router->generate('api_v1_github_callback', [], RouterInterface::ABSOLUTE_URL);
			$response    = $client->post(strtr('/repos/:owner/:repo/hooks', [
				':owner' => $owner,
				':repo'  => $repo,
			]), [
				'json' => [
					'name'   => 'web',
					'config' => [
						'url'          => $callbackUrl,
						'content_type' => 'json',
					],
					'events' => $events,
					'active' => true,
				],
			]);

			$data = json_decode((string)$response->getBody(), true);

			return (int)$data['id'];
		}
		catch (RequestException $exception)
		{
			$this->logger->error($exception->getMessage(), [
				'exception' => $exception,
			]);

			throw new \RuntimeException(vsprintf('Cannot create Github webhook for %s/%s', [
				$owner,
				$repo,
			]));
		}
	}

	/** {@inheritdoc} */
	public function deleteHook(string $owner, string $repo, int $webhookId): bool
	{
		$client = $this->getClient();

		try
		{
			$response = $client->delete(strtr('/repos/:owner/:repo/hooks/:id', [
				':owner' => $owner,
				':repo'  => $repo,
				':id'    => $webhookId,
			]));

			return 204 === $response->getStatusCode();
		}
		catch (BadResponseException $badResponseException)
		{
			$badRequestResponse = $badResponseException->getResponse();

			if (404 === $badRequestResponse->getStatusCode())
			{
				return true;
			}

			throw $badResponseException;
		}
		catch (RequestException $exception)
		{
			$this->logger->error($exception->getMessage(), [
				'exception' => $exception,
			]);

			return false;
		}
	}

	/** {@inheritdoc} */
	public function getCommits(string $owner, string $repo, string $ref): array
	{
		$client = $this->getClient();

		$response = $client->get(strtr('/repos/:owner/:repo/commits?sha=:sha&per_page=:perPage&page=:page', [
			':owner'   => $owner,
			':repo'    => $repo,
			':sha'     => $ref,
			':perPage' => 100,
			':page'    => 1,
		]));

		$commits = json_decode((string)$response->getBody(), true);

		return array_map(function (array $commit)
		{
			return [
				'sha'       => $commit['sha'],
				'commit'    => [
					'message'       => $commit['commit']['message'],
					'comment_count' => $commit['commit']['comment_count'],
				],
				'html_url'  => $commit['html_url'],
				'author'    => [
					'login'      => $commit['author']['login'],
					'avatar_url' => $commit['author']['avatar_url'],
					'html_url'   => $commit['author']['html_url'],
				],
				'committer' => [
					'login'      => $commit['committer']['login'],
					'avatar_url' => $commit['committer']['avatar_url'],
					'html_url'   => $commit['committer']['html_url'],
				],
			];
		}, $commits);
	}

	/** {@inheritdoc} */
	public function getCommitsDiff(string $owner, string $repo, string $baseRef, string $headRef): array
	{
		$client = $this->getClient();

		$response = $client->get(strtr('/repos/:owner/:repo/compare/:base...:head', [
			':owner' => $owner,
			':repo'  => $repo,
			':base'  => $baseRef,
			':head'  => $headRef,
		]));

		$diff = json_decode((string)$response->getBody(), true);

		return [
			'status'        => $diff['status'],
			'ahead_by'      => $diff['ahead_by'],
			'behind_by'     => $diff['behind_by'],
			'total_commits' => $diff['total_commits'],
		];
	}

	/** {@inheritdoc} */
	public function getStatuses(string $owner, string $repo, string $ref): array
	{
		$generateRequest  = function (int $page) use ($owner, $repo, $ref): RequestInterface
		{
			return new Request('GET',
				strtr('/repos/:owner/:repo/commits/:ref/statuses?since=:since&per_page=:perPage&page=:page', [
					':owner'   => $owner,
					':repo'    => $repo,
					':ref'     => $ref,
					':perPage' => 100,
					':page'    => $page,
				]));
		};
		$generateRequests = function (int $maxPage, int $minPage = 2) use ($generateRequest): \Generator
		{
			while ($maxPage-- >= $minPage)
			{
				yield $generateRequest($maxPage);
			}
		};

		$client        = $this->getClient();
		$firstResponse = $client->send($generateRequest(1));

		/** @var ResponseInterface[] $responses */
		$responses = [];
		if ($firstResponse->hasHeader('Link'))
		{
			$links          = \GuzzleHttp\Psr7\parse_header($firstResponse->getHeader('Link'));
			$lastPageNumber = 1;
			foreach ($links as $link)
			{
				if ('last' === $link['rel'] && preg_match('/(?:\?|&)page=(\d+)/i', $link[0], $matches))
				{
					$lastPageNumber = (int)$matches[1];
					break;
				}
			}

			if ($lastPageNumber > 1)
			{
				$pool = new Pool($client, $generateRequests($lastPageNumber), [
					'concurrency' => 5,
					'fulfilled'   => function ($response, $index) use (&$responses)
					{
						$responses[$index] = $response;
					},
				]);

				$promise = $pool->promise();
				$promise->wait();
			}
		}
		array_unshift($responses, $firstResponse);

		$data = array_reduce(array_map(function (ResponseInterface $response)
		{
			$statuses = json_decode((string)$response->getBody(), true);

			return array_map(function (array $status)
			{
				return [
					'target_url'  => $status['target_url'],
					'state'       => $status['state'],
					'description' => $status['description'],
					'context'     => $status['context'],
					'created_at'  => $status['created_at'],
					'updated_at'  => $status['updated_at'],
				];
			}, $statuses);
		}, $responses), function (array $initial, $sequence)
		{
			return array_merge($initial, $sequence);
		}, []);

		return $data;
	}

	/** {@inheritdoc} */
	public function getLatestDeployment(string $owner, string $repo, string $stage): array
	{
		$client = $this->getClient();

		$response = $client->get(strtr('/repos/:owner/:repo/deployments', [
			':owner' => $owner,
			':repo'  => $repo,
		]), [
			'query' => [
				'environment' => $stage,
				'per_page'    => 1,
				'page'        => 1,
			],
		]);

		$deployments = json_decode((string)$response->getBody(), true);

		if (0 === count($deployments))
		{
			return [];
		}

		$deployment = array_shift($deployments);

		return [
			'id'          => $deployment['id'],
			'ref'         => $deployment['ref'],
			'task'        => $deployment['task'],
			'payload'     => $deployment['payload'],
			'environment' => $deployment['environment'],
			'description' => $deployment['description'],
			'created_at'  => $deployment['created_at'],
			'updated_at'  => $deployment['updated_at'],
			'creator'     => [
				'login'      => $deployment['creator']['login'],
				'avatar_url' => $deployment['creator']['avatar_url'],
				'html_url'   => $deployment['creator']['html_url'],
			],
		];
	}

	/** {@inheritdoc} */
	public function getDeploymentStatuses(string $owner, string $repo, int $deploymentId): array
	{
		$client = $this->getClient();

		$response = $client->get(strtr('/repos/:owner/:repo/deployments/:id/statuses', [
			':owner' => $owner,
			':repo'  => $repo,
			':id'    => $deploymentId,
		]), [
			'query' => [
				'per_page' => 100,
				'page'     => 1,
			],
		]);

		$statuses = json_decode((string)$response->getBody(), true);

		$data = [];
		foreach ($statuses as $status)
		{
			$data[] = [
				'id'          => $status['id'],
				'state'       => $status['state'],
				'description' => $status['description'],
				'target_url'  => $status['target_url'],
				'created_at'  => $status['created_at'],
				'updated_at'  => $status['updated_at'],
				'creator'     => [
					'login'      => $status['creator']['login'],
					'avatar_url' => $status['creator']['avatar_url'],
					'html_url'   => $status['creator']['html_url'],
				],
			];
		}


		return $data;
	}

	protected function getClient(): GuzzleClient
	{
		$randomCredentials = $this->credentials[array_rand($this->credentials)];
		if (null === $this->client)
		{
			$this->client = new GuzzleClient([
				'base_uri' => 'https://api.github.com/',
				'auth'     => [
					$randomCredentials['username'],
					$randomCredentials['password'],
				],
			]);
		}

		return $this->client;
	}
}
