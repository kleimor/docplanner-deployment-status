import axios from "axios";

export const FETCH_COMMITS_STARTED = 'FETCH_COMMITS_STARTED';
export const FETCH_COMMITS_FINISHED = 'FETCH_COMMITS_FINISHED';
export const FETCH_COMMITS_FAILED = 'FETCH_COMMITS_FAILED';

const fetchCommitsStarted = (owner, repo, stage) => ({
	type: FETCH_COMMITS_STARTED,
	owner: owner,
	repo: repo,
	stage: stage,
});

const fetchCommitsFinished = (owner, repo, stage, commits) => ({
	type: FETCH_COMMITS_FINISHED,
	owner: owner,
	repo: repo,
	stage: stage,
	commits: commits,
});

const fetchCommitsFailed = (owner, repo, stage, error) => ({
	type: FETCH_COMMITS_FAILED,
	owner: owner,
	repo: repo,
	stage: stage,
	error: error,
});

export const fetchCommits = (owner, repo, stage) => (dispatch) => {
	dispatch(fetchCommitsStarted(owner, repo, stage));

	return axios
		.get(`/api/1/projects/${owner}/${repo}/${stage}/commits`)
		.then((response) => dispatch(fetchCommitsFinished(owner, repo, stage, response.data)))
		.catch((error) => dispatch(fetchCommitsFailed(owner, repo, stage, error)));
};
