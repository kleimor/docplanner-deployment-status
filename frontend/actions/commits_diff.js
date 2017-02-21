import * as jQuery from "jquery";

export const FETCH_COMMITS_DIFF_STARTED = 'FETCH_COMMITS_DIFF_STARTED';
export const FETCH_COMMITS_DIFF_FINISHED = 'FETCH_COMMITS_DIFF_FINISHED';
export const FETCH_COMMITS_DIFF_FAILED = 'FETCH_COMMITS_DIFF_FAILED';

const fetchCommitsDiffStarted = (owner, repo, stage) => ({
	type: FETCH_COMMITS_DIFF_STARTED,
	owner: owner,
	repo: repo,
	stage: stage,
});

const fetchCommitsDiffFinished = (owner, repo, stage, diff) => ({
	type: FETCH_COMMITS_DIFF_FINISHED,
	owner: owner,
	repo: repo,
	stage: stage,
	diff: diff
});

const fetchCommitsDiffFailed = (owner, repo, stage, error) => ({
	type: FETCH_COMMITS_DIFF_FAILED,
	owner: owner,
	repo: repo,
	stage: stage,
	error: error,
});

export const fetchCommitsDiff = (owner, repo, stage) => (dispatch) => {
	dispatch(fetchCommitsDiffStarted(owner, repo, stage));

	return jQuery.ajax({
		url: `/api/1/projects/${owner}/${repo}/${stage}/commits_diff`,
		dataType: 'json',
		success: (data) => {
			dispatch(fetchCommitsDiffFinished(owner, repo, stage, data));
		},
		error: (data) => {
			dispatch(fetchCommitsDiffFailed(owner, repo, stage, data));
		}
	});
};
