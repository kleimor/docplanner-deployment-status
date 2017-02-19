import {FETCH_COMMITS_STARTED, FETCH_COMMITS_FINISHED, FETCH_COMMITS_FAILED} from "../actions/commits"
import {REMOVE_PROJECT} from "../actions/projects";
import {REMOVE_STAGE} from "../actions/stages";

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

		case REMOVE_PROJECT:
			return (() => {
				let newForProject = {};
				for (let key in state.forProject) {
					if (0 !== key.indexOf(`${action.owner}/${action.repo}/`)) {
						newForProject[key] = state.forProject[key];
					}
				}
				return Object.assign(
					{},
					state,
					{
						forProject: newForProject,
					}
				);
			})();
			break;

		case REMOVE_STAGE:
			return (() => {
				let newForProject = {};
				for (let key in state.forProject) {
					if (`${action.owner}/${action.repo}/${action.stage}` !== key) {
						newForProject[key] = state.forProject[key];
					}
				}
				return Object.assign(
					{},
					state,
					{
						forProject: newForProject,
					}
				);
			})();
			break;
	}

	return state;
};

export default projectsReducer;
