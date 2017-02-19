export const REMOVE_STAGE = 'REMOVE_STAGE';

export const removeStage = (owner, repo, stage) => (dispatch) => {
	dispatch({
		type: REMOVE_STAGE,
		owner: owner,
		repo: repo,
		stage: stage
	});
};
