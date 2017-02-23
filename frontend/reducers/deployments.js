import {
	FETCH_LATEST_DEPLOYMENT_STARTED,
	FETCH_LATEST_DEPLOYMENT_FINISHED,
	FETCH_LATEST_DEPLOYMENT_FAILED
} from "../actions/deployments"
import {REMOVE_PROJECT} from "../actions/projects";
import {REMOVE_STAGE} from "../actions/stages";

const initialState = {
	forProject: {},
};

const deploymentsReducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_LATEST_DEPLOYMENT_STARTED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
				latestDeployment: {},
				isRecent: false,
				isLoading: true,
				updatedAt: new Date,
			};

			return newState;
		}

		case FETCH_LATEST_DEPLOYMENT_FAILED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
				latestDeployment: state.hasOwnProperty(`${action.owner}/${action.repo}/${action.stage}`) ?
					state[`${action.owner}/${action.repo}/${action.stage}`]
					:
					{},
				isRecent: false,
				isLoading: false,
				updatedAt: new Date,
			};

			return newState;
		}

		case FETCH_LATEST_DEPLOYMENT_FINISHED: {
			let newState = {...state};
			newState.forProject[`${action.owner}/${action.repo}/${action.stage}`] = {
				latestDeployment: action.deployment,
				isRecent: true,
				isLoading: false,
				updatedAt: new Date,
			};

			return newState;
		}

		case REMOVE_PROJECT: {
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
		}

		case REMOVE_STAGE: {
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
		}
	}

	return state;
};

export default deploymentsReducer;
