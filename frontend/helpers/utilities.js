import {clearProjectCache} from "../actions/projects";
import {clearStageCache} from "../actions/stages";
import {fetchCommits} from "../actions/commits";
import {fetchCommitsDiff} from "../actions/commits_diff";
import {fetchStatuses} from "../actions/statuses";
import {fetchLatestDeployment} from "../actions/deployments";
import appStore from "../stores/app";
import {fetchHooks} from "../actions/hooks";

export const reloadProjectData = (owner, repo) => {
	const state = appStore.getState();

	clearProjectCache(owner, repo)
		.then(() => {
			state.projects.projects.forEach((project) => {
				if (project.owner === owner && project.repo === repo) {
					appStore.dispatch(fetchHooks(owner, repo));
					project.stages.forEach((stage) => reloadStageData(owner, repo, stage.name))
				}
			})
		});
};

export const reloadStageData = (owner, repo, stage) => {
	clearStageCache(owner, repo, stage)
		.then(() => loadStageData(owner, repo, stage));
};

export const loadProjectData = (owner, repo) => {
	const state = appStore.getState();

	state.projects.projects.forEach((project) => {
		if (project.owner === owner && project.repo === repo) {
			appStore.dispatch(fetchHooks(owner, repo));
			project.stages.forEach((stage) => loadStageData(owner, repo, stage.name))
		}
	})
};

export const loadStageData = (owner, repo, stage) => {
	appStore.dispatch(fetchCommits(owner, repo, stage));
	appStore.dispatch(fetchCommitsDiff(owner, repo, stage));
	appStore.dispatch(fetchStatuses(owner, repo, stage));
	appStore.dispatch(fetchLatestDeployment(owner, repo, stage));
};
