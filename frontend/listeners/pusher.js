import Pusher from 'pusher-js';
import appStore from "../stores/app";
import {reloadProjectData, reloadStageData} from "../helpers/utilities";
import {removeProject} from "../actions/projects";
import {removeStage} from "../actions/stages";

const pusher = ((appConfig) => (
	new Pusher(appConfig.pusher.key, {
		cluster: appConfig.pusher.cluster,
		encrypted: true,
		keepAlive: true
	})
))(window.appConfig);

const publicChannel = pusher.subscribe('public');

publicChannel.bind('github.push', (event) => {
	const owner = event["payload"]["repository"]["owner"]["name"];
	const repo = event["payload"]["repository"]["name"];

	reloadProjectData(owner, repo);
});

publicChannel.bind('github.status', (event) => {
	const owner = event["payload"]["repository"]["owner"]["name"];
	const repo = event["payload"]["repository"]["name"];

	reloadProjectData(owner, repo);
});

publicChannel.bind('github.deployment', (event) => {
	const owner = event["payload"]["repository"]["owner"]["name"];
	const repo = event["payload"]["repository"]["name"];
	const stage = event["payload"]["deployment"]["environment"];

	reloadStageData(owner, repo, stage);
});

publicChannel.bind('github.deployment_status', (event) => {
	const owner = event["payload"]["repository"]["owner"]["login"];
	const repo = event["payload"]["repository"]["name"];
	const stage = event["payload"]["deployment"]["environment"];

	reloadStageData(owner, repo, stage);
});

publicChannel.bind('project.deleted', (event) => {
	const owner = event["project"]["owner"];
	const repo = event["project"]["repo"];

	appStore.dispatch(removeProject(owner, repo));
});

publicChannel.bind('stage.deleted', (event) => {
	const owner = event["project"]["owner"];
	const repo = event["project"]["repo"];
	const stageName = event["stage"]["name"];

	appStore.dispatch(removeStage(owner, repo, stageName));
});

publicChannel.bind('project.github_webhook.created', (event) => {
	const owner = event["project"]["owner"];
	const repo = event["project"]["repo"];

	reloadProjectData(owner, repo);
});

publicChannel.bind('project.github_webhook.deleted', (event) => {
	const owner = event["project"]["owner"];
	const repo = event["project"]["repo"];

	reloadProjectData(owner, repo);
});
