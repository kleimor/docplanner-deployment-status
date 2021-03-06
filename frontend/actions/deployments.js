import axios from "axios";

export const FETCH_LATEST_DEPLOYMENT = 'FETCH_LATEST_DEPLOYMENT';
export const FETCH_LATEST_DEPLOYMENT_STARTED = 'FETCH_LATEST_DEPLOYMENT_STARTED';
export const FETCH_LATEST_DEPLOYMENT_FINISHED = 'FETCH_LATEST_DEPLOYMENT_FINISHED';
export const FETCH_LATEST_DEPLOYMENT_FAILED = 'FETCH_LATEST_DEPLOYMENT_FAILED';

const fetchLatestDeploymentStarted = (owner, repo, stage) => ({
	type: FETCH_LATEST_DEPLOYMENT_STARTED,
	owner: owner,
	repo: repo,
	stage: stage,
});

const fetchLatestDeploymentFinished = (owner, repo, stage, deployment) => ({
	type: FETCH_LATEST_DEPLOYMENT_FINISHED,
	owner: owner,
	repo: repo,
	stage: stage,
	deployment: deployment,
});

const fetchLatestDeploymentFailed = (owner, repo, stage, error) => ({
	type: FETCH_LATEST_DEPLOYMENT_FAILED,
	owner: owner,
	repo: repo,
	stage: stage,
	error: error,
});

export const fetchLatestDeployment = (owner, repo, stage) => (dispatch) => {
	dispatch(fetchLatestDeploymentStarted(owner, repo, stage));

	return axios
		.get(`/api/1/projects/${owner}/${repo}/${stage}/latest_deployment`)
		.then((response) => dispatch(fetchLatestDeploymentFinished(owner, repo, stage, response.data)))
		.catch((error) => dispatch(fetchLatestDeploymentFailed(owner, repo, stage, error)));
};
