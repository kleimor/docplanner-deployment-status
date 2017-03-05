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
	const {project: {owner, repo}} = event;
	reloadProjectData(owner, repo);
});

publicChannel.bind('github.status', (event) => {
	const {project: {owner, repo}} = event;
	reloadProjectData(owner, repo);
});

publicChannel.bind('github.deployment', (event) => {
	const {project: {owner, repo}, stage: {name: stage}} = event;
	reloadStageData(owner, repo, stage);
});

publicChannel.bind('github.deployment_status', (event) => {
	const {project: {owner, repo}, stage: {name: stage}} = event;
	reloadStageData(owner, repo, stage);
});

publicChannel.bind('project.deleted', (event) => {
	const {project: {owner, repo}} = event;
	appStore.dispatch(removeProject(owner, repo));
});

publicChannel.bind('stage.deleted', (event) => {
	const {project: {owner, repo}, stage: {name: stage}} = event;
	appStore.dispatch(removeStage(owner, repo, stage));
});

publicChannel.bind('project.github_webhook.created', (event) => {
	const {project: {owner, repo}} = event;
	reloadProjectData(owner, repo);
});

publicChannel.bind('project.github_webhook.deleted', (event) => {
	const {project: {owner, repo}} = event;
	reloadProjectData(owner, repo);
});
