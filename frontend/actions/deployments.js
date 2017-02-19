export const FETCH_DEPLOYMENTS = 'FETCH_DEPLOYMENTS';
export const FETCH_DEPLOYMENTS_STARTED = 'FETCH_DEPLOYMENTS_STARTED';
export const FETCH_DEPLOYMENTS_FINISHED = 'FETCH_DEPLOYMENTS_FINISHED';
export const FETCH_DEPLOYMENTS_FAILED = 'FETCH_DEPLOYMENTS_FAILED';

const fetchDeploymentsStarted = (owner, repo, stage) => ({
	type: FETCH_DEPLOYMENTS_STARTED,
	owner: owner,
	repo: repo,
	stage: stage,
});

const fetchDeploymentsFinished = (owner, repo, stage, deployments) => ({
	type: FETCH_DEPLOYMENTS_FINISHED,
	owner: owner,
	repo: repo,
	stage: stage,
	deployments: deployments,
});

const fetchDeploymentsFailed = (owner, repo, stage, error) => ({
	type: FETCH_DEPLOYMENTS_FAILED,
	owner: owner,
	repo: repo,
	stage: stage,
	error: error,
});

export const fetchDeployments = (owner, repo, stage) => (dispatch) => {
	dispatch(fetchDeploymentsStarted(owner, repo, stage));

	return jQuery.ajax({
		url: `/api/1/projects/${owner}/${repo}/${stage}/deployments`,
		dataType: 'json',
		success: (data) => {
			dispatch(fetchDeploymentsFinished(owner, repo, stage, data));
		},
		error: (data) => {
			dispatch(fetchDeploymentsFailed(owner, repo, stage, data));
		}
	});
};
