export const TOGGLE_STARRED = 'TOGGLE_STARRED';

export const toggleStarred = (owner, repo) => (dispatch) => {
	dispatch({
		type: TOGGLE_STARRED,
		owner: owner,
		repo: repo
	});
};
