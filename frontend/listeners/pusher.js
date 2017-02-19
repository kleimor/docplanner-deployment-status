import Pusher from 'pusher-js';
import appStore from "../stores/app";
import * as CommitActions from "../actions/commits";
import * as StatusActions from "../actions/statuses";
import * as ProjectActions from "../actions/projects";
import * as StageActions from "../actions/stages";

let pusher = ((appConfig) => (
	new Pusher(appConfig.pusher.key, {
		cluster: appConfig.pusher.cluster,
		encrypted: true,
		keepAlive: true
	})
))(window.appConfig);

const publicChannel = pusher.subscribe('public');

publicChannel.bind('github.push', (event) => {
	const state = appStore.getState();
	const owner = event["repository"]["owner"]["name"];
	const repo = event["repository"]["name"];

	state.projects.projects.forEach((project) => {
		if (
			project.owner === owner
			&& project.repo === repo
		) {
			project.stages.forEach((stage) => {
				appStore.dispatch(CommitActions.fetchCommits(owner, repo, stage.name));
			});
		}
	});
});

publicChannel.bind('github.status', (event) => {
	const state = appStore.getState();
	const owner = event["repository"]["owner"]["name"];
	const repo = event["repository"]["name"];

	state.projects.projects.forEach((project) => {
		if (
			project.owner === owner
			&& project.repo === repo
		) {
			project.stages.forEach((stage) => {
				appStore.dispatch(StatusActions.fetchStatuses(owner, repo, stage.name));
			});
		}
	});
});

// TODO: listen to deployments
// TODO: listen to deployment statuses

publicChannel.bind('project.deleted', (event) => {
	const owner = event["project"]["owner"];
	const repo = event["project"]["repo"];

	appStore.dispatch(ProjectActions.removeProject(owner, repo));
});

publicChannel.bind('stage.deleted', (event) => {
	const owner = event["project"]["owner"];
	const repo = event["project"]["repo"];
	const stageName = event["stage"]["name"];

	appStore.dispatch(StageActions.removeStage(owner, repo, stageName));
});

// TODO: listen to adding a project
// TODO: listen to adding a stage
