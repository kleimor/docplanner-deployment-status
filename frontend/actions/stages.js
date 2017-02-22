import * as jQuery from "jquery";

export const REMOVE_STAGE = 'REMOVE_STAGE';

export const removeStage = (owner, repo, stage) => (dispatch) => {
	dispatch({
		type: REMOVE_STAGE,
		owner: owner,
		repo: repo,
		stage: stage
	});
};

export const clearStageCache = (owner, repo, stage, onCacheCleared = () => {}) => (dispatch) => {
	jQuery.ajax({
		url: `/api/1/projects/${owner}/${repo}/${stage}/cache`,
		method: 'DELETE',
		success: () => {
			onCacheCleared();
		}
	});
};
