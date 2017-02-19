import {FETCH_STATUSES_STARTED, FETCH_STATUSES_FINISHED, FETCH_STATUSES_FAILED} from "../actions/statuses"

const initialState = {
	forProject: {},
};

const statusesReducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_STATUSES_STARTED:
			return (() => {
				let newState = {...state};
				newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
					isLoading: true,
					updatedAt: new Date,
				};

				return newState;
			})();
			break;

		case FETCH_STATUSES_FAILED:
			return (() => {
				let newState = {...state};
				newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
					statuses: state.hasOwnProperty(`${action.owner}/${action.repo}/${action.stage}`) ?
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

		case FETCH_STATUSES_FINISHED:
			return (() => {
				let newState = {...state};
				newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
					statuses: action.statuses,
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

export default statusesReducer;
