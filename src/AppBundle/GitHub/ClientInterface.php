<?php

declare(strict_types=1);

namespace AppBundle\GitHub;

interface ClientInterface
{
	public function createHook(string $owner, string $repo, array $events): int;

	public function deleteHook(string $owner, string $repo, int $webhookId): bool;

	public function getCommits(string $owner, string $repo, string $ref, int $daysBack): array;

	public function getCommitsDiff(string $owner, string $repo, string $baseRef, string $headRef): array;

	public function getStatuses(string $owner, string $repo, string $ref): array;

	public function getLatestDeployment(string $owner, string $repo, string $stage): array;

	public function getDeploymentStatuses(string $owner, string $repo, int $deploymentId): array;
}
