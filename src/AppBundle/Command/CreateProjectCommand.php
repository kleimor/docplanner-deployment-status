<?php

declare(strict_types=1);

namespace AppBundle\Command;

use AppBundle\Event\Project\ProjectCreatedEvent;
use AppBundle\Event\Project\ProjectCreatingEvent;
use AppBundle\Model\ProjectQuery;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateProjectCommand extends ContainerAwareCommand
{
	const ARG_NAME       = 'name';
	const ARG_REPO_NAME  = 'repo-owner';
	const ARG_REPO_OWNER = 'repo-name';
	const ARG_REPO_URI   = 'repo-uri';

	/** @var SymfonyStyle */
	protected $io;

	/** {@inheritdoc} */
	protected function configure()
	{
		$this->setName('project:create');
		$this->setDescription('Create project');
		$this->addArgument(self::ARG_NAME, InputArgument::REQUIRED, 'Project name');
		$this->addArgument(self::ARG_REPO_OWNER, InputArgument::REQUIRED, 'Repository owner');
		$this->addArgument(self::ARG_REPO_NAME, InputArgument::REQUIRED, 'Repository name');
		$this->addArgument(self::ARG_REPO_URI, InputArgument::REQUIRED, 'Repository URI');
	}

	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		$this->io = new SymfonyStyle($input, $output);
	}


	/** {@inheritdoc} */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$project = (new ProjectQuery)
			->filterByRepositoryOwner($input->getArgument(self::ARG_REPO_OWNER))
			->filterByRepositoryName($input->getArgument(self::ARG_REPO_NAME))
			->findOne();

		if (null == $project)
		{
			$con = $this->getContainer()->get('propel.connection.default');
			$con->beginTransaction();
			try
			{
				$project
					->setName($input->getArgument(self::ARG_NAME))
					->setRepositoryOwner($input->getArgument(self::ARG_REPO_OWNER))
					->setRepositoryName($input->getArgument(self::ARG_REPO_NAME))
					->setRepositoryUri($input->getArgument(self::ARG_REPO_URI))
					->save();

				$event = new ProjectCreatingEvent($project);
				$this->getContainer()->get('event_dispatcher')->dispatch(ProjectCreatingEvent::getEventName(), $event);

				$con->commit();
			}
			catch (\Exception $exception)
			{
				$con->rollBack();
				throw $exception;
			}

			$event = new ProjectCreatedEvent($project);
			$this->getContainer()->get('event_dispatcher')->dispatch(ProjectCreatedEvent::getEventName(), $event);

			$this->io->success('Project created');

			return 0;
		}

		$this->io->warning('Project already exists');

		return 1;
	}

	/** {@inheritdoc} */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$helperSet = $this->getHelperSet();
		$helperSet->set(new SymfonyQuestionHelper, 'question');
		/** @var SymfonyQuestionHelper $questionHelper */
		$questionHelper = $helperSet->get('question');

		foreach ([
			self::ARG_NAME       => 'Project name',
			self::ARG_REPO_OWNER => 'Repository owner',
			self::ARG_REPO_NAME  => 'Repository name',
		] as $param => $description)
		{
			$value = $input->getArgument($param);
			if (null !== $value)
			{
				continue;
			}

			$value = $questionHelper->ask($input, $output, new Question($description));
			$input->setArgument($param, $value);
		}

		if (null === $input->getArgument(self::ARG_REPO_URI))
		{
			$defaultUri = vsprintf('https://github.com/%s/%s.git', [
				$input->getArgument(self::ARG_REPO_OWNER),
				$input->getArgument(self::ARG_REPO_NAME),
			]);
			$repoUri    = $questionHelper->ask($input, $output, new Question('Repository URI', $defaultUri));
			$input->setArgument(self::ARG_REPO_URI, $repoUri);
		}
	}
}
