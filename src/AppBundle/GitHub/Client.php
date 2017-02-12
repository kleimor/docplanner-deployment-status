<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\RouterInterface;

class Client implements LoggerAwareInterface
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

			throw new \RuntimeException('Cannot create Github webhook');
		}
	}

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
		catch (RequestException $exception)
		{
			$this->logger->error($exception->getMessage(), [
				'exception' => $exception,
			]);

			return false;
		}
	}

	public function getCommits(string $owner, string $repo, string $ref, int $daysBack): array
	{
		$generateRequest  = function (int $page) use ($owner, $repo, $ref, $daysBack): RequestInterface
		{
			return new Request('GET',
				strtr('/repos/:owner/:repo/commits?sha=:sha&since=:since&per_page=:perPage&page=:page', [
					':owner'   => $owner,
					':repo'    => $repo,
					':sha'     => $ref,
					':since'   => (new \DateTime("-{$daysBack} days 00:00:00"))->format(\DateTime::ISO8601),
					':perPage' => 100,
					':page'    => $page,
				]));
		};
		$generateRequests = function (int $maxPage, int $minPage = 2) use (
			$generateRequest,
			$owner,
			$repo,
			$ref,
			$daysBack
		): \Generator
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
		}, $responses), function (array $initial, $sequence)
		{
			return array_merge($initial, $sequence);
		}, []);

		return $data;
	}

	public function getStatuses(string $owner, string $repo, string $ref, int $daysBack): array
	{
		$generateRequest  = function (int $page) use ($owner, $repo, $ref, $daysBack): RequestInterface
		{
			return new Request('GET',
				strtr('/repos/:owner/:repo/commits/:ref/statuses?since=:since&per_page=:perPage&page=:page', [
					':owner'   => $owner,
					':repo'    => $repo,
					':ref'     => $ref,
					':since'   => (new \DateTime("-{$daysBack} days 00:00:00"))->format(\DateTime::ISO8601),
					':perPage' => 100,
					':page'    => $page,
				]));
		};
		$generateRequests = function (int $maxPage, int $minPage = 2) use (
			$generateRequest,
			$owner,
			$repo,
			$ref,
			$daysBack
		): \Generator
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

//	public function getCommits(string $owner, string $repo, string $branchName)
//	{
//		$client = $this->getClient();
//
//		$response = $client->get(strtr('/repos/:owner/:repo/commits/:branch', [
//			':owner' => $owner,
//			':repo' => $repo,
//			':ref' => $branchName,
//		]), [
//
//		]);
//
//		$commits = $this->getCommits('-7 days', $branchName);
//
//		list($prodHash, $stagingHash) = $this->getProdStagingHashes();
//		$prodWasHere    = false;
//		$stagingWasHere = false;
//
//		$data = [];
//		foreach ($commits as $commit)
//		{
//			$prodWasHere    = $prodWasHere || $prodHash == $commit['sha'];
//			$stagingWasHere = $stagingWasHere || $stagingHash == $commit['sha'];
//
//			if (15 <= count($data))
//			{
//				break;
//			}
//
//			$status = $this->getCommitStatus($commit['sha']);
//
//			$statuses = [];
//			foreach ($status['statuses'] as $oneStatus)
//			{
//				$name       = explode('/', $oneStatus['context']);
//				$name       = end($name);
//				$statuses[] = [
//					'name'       => $name,
//					'target_url' => $oneStatus['target_url'],
//					'label'      => self::$stateLabelNames[$oneStatus['state']],
//				];
//			}
//
//			$data[] = [
//				'html_url'       => $commit['html_url'],
//				'sha'            => $status['sha'],
//				'message'        => $commit['commit']['message'],
//				'name'           => $commit['commit']['committer']['name'],
//				'date'           => (new \DateTime($commit['commit']['committer']['date']))->setTimezone(new \DateTimeZone('Europe/Warsaw'))->format('Y-m-d H:i:s'),
//				'state'          => $status['state'],
//				'label'          => [
//					'name' => self::$stateLabelNames[$status['state']],
//					'text' => strtoupper($status['state']),
//				],
//				'statuses'       => $statuses,
//				'prodWasHere'    => $prodWasHere,
//				'stagingWasHere' => $stagingWasHere,
//			];
//		}
//
//		return $data;
//	}

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
