import {FETCH_COMMITS_STARTED, FETCH_COMMITS_FINISHED, FETCH_COMMITS_FAILED} from "../actions/commits"

const initialState = {
	forProject: {},
};

const projectsReducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_COMMITS_STARTED:
			return (() => {
				let newState = {...state};
				newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
					isLoading: true,
					updatedAt: new Date,
				};

				return newState;
			})();
			break;

		case FETCH_COMMITS_FAILED:
			return (() => {
				let newState = {...state};
				newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
					commits: state.hasOwnProperty(`${action.owner}/${action.repo}/${action.stage}`) ?
						state[`${action.owner}/${action.repo}/${action.stage}`]
						:
						[],
					isRecent: false,
					isLoading: false,
					updatedAt: new Date,
				};

				return newState;
			})();
			break;

		case FETCH_COMMITS_FINISHED:
			return (() => {
				let newState = {...state};
				newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
					commits: action.commits,
					isRecent: true,
					isLoading: false,
					updatedAt: new Date,
				};

				return newState;
			})();
			break;
	}

	return state;
};

export default projectsReducer;
