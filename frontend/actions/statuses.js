import axios from "axios";

export const FETCH_STATUSES_STARTED = 'FETCH_STATUSES_STARTED';
export const FETCH_STATUSES_FINISHED = 'FETCH_STATUSES_FINISHED';
export const FETCH_STATUSES_FAILED = 'FETCH_STATUSES_FAILED';

const fetchStatusesStarted = (owner, repo, stage) => ({
	type: FETCH_STATUSES_STARTED,
	owner: owner,
	repo: repo,
	stage: stage,
});

const fetchStatusesFinished = (owner, repo, stage, statuses) => ({
	type: FETCH_STATUSES_FINISHED,
	owner: owner,
	repo: repo,
	stage: stage,
	statuses: statuses,
});

const fetchStatusesFailed = (owner, repo, stage, error) => ({
	type: FETCH_STATUSES_FAILED,
	owner: owner,
	repo: repo,
	stage: stage,
	error: error,
});

export const fetchStatuses = (owner, repo, stage) => (dispatch) => {
	dispatch(fetchStatusesStarted(owner, repo, stage));

	return axios
		.get(`/api/1/projects/${owner}/${repo}/${stage}/statuses`)
		.then((response) => dispatch(fetchStatusesFinished(owner, repo, stage, response.data)))
		.catch((error) => dispatch(fetchStatusesFailed(owner, repo, stage, error)));
};
