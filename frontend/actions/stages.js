import axios from "axios";

export const REMOVE_STAGE = 'REMOVE_STAGE';

export const removeStage = (owner, repo, stage) => (dispatch) => {
	dispatch({
		type: REMOVE_STAGE,
		owner: owner,
		repo: repo,
		stage: stage
	});
};

export const clearStageCache = (owner, repo, stage) => {
	return axios.delete(`/api/1/projects/${owner}/${repo}/${stage}/cache`);
};
