import {
	FETCH_HOOKS_STARTED,
	FETCH_HOOKS_FINISHED,
	FETCH_HOOKS_FAILED,
	INSTALL_HOOK_STARTED,
	INSTALL_HOOK_FINISHED,
	INSTALL_HOOK_FAILED,
} from "../actions/hooks"
import {REMOVE_PROJECT} from "../actions/projects";

const initialState = {
	forProject: {},
};

const hooksReducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_HOOKS_STARTED:
			{
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}`] = {
				hooks: [],
				isRecent: false,
				isLoading: true,
				updatedAt: new Date,
			};

			return newState;
		}

		case FETCH_HOOKS_FAILED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}`] = {
				hooks: state.hasOwnProperty(`${action.owner}/${action.repo}`) ?
					state[`${action.owner}/${action.repo}`]
					:
					[],
				isRecent: false,
				isLoading: false,
				updatedAt: new Date,
			};

			return newState;
		}

		case FETCH_HOOKS_FINISHED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}`] = {
				hooks: action.hooks,
				isRecent: true,
				isLoading: false,
				updatedAt: new Date,
			};

			return newState;
		}

		case INSTALL_HOOK_STARTED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}`] = {
				isRecent: false,
				isLoading: true,
				updatedAt: new Date,
			};

			return newState;
		}

		case INSTALL_HOOK_FAILED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}`] = {
				hooks: state.hasOwnProperty(`${action.owner}/${action.repo}`) ?
					state[`${action.owner}/${action.repo}`]
					:
					[],
				isRecent: false,
				isLoading: false,
				updatedAt: new Date,
			};

			return newState;
		}

		case INSTALL_HOOK_FINISHED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}`] = {
				hooks: action.hooks,
				isRecent: true,
				isLoading: false,
				updatedAt: new Date,
			};

			return newState;
		}

		case REMOVE_PROJECT: {
			let newForProject = {};
			for (let key in state.forProject) {
				if (0 !== key.indexOf(`${action.owner}/${action.repo}`)) {
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
		}
	}

	return state;
};

export default hooksReducer;
