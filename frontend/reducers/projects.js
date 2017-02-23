import {FETCH_PROJECTS_STARTED, FETCH_PROJECTS_FINISHED, REMOVE_PROJECT, ADD_PROJECT} from "../actions/projects"
import {REMOVE_STAGE} from "../actions/stages";

const initialState = {
	projects: [],
	isLoading: false,
};

const projectsReducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_PROJECTS_STARTED: {
			return Object.assign(
				{},
				state,
				{
					projects: [],
					isLoading: true,
					updatedAt: new Date,
				}
			);
		}

		case FETCH_PROJECTS_FINISHED: {
			return Object.assign(
				{},
				state,
				{
					projects: action.projects,
					isLoading: false,
					updatedAt: new Date,
				}
			);
		}

		case REMOVE_PROJECT: {
			return Object.assign(
				{},
				state,
				{
					projects: state.projects.filter((project) => {
						return !(project.owner === action.owner && project.repo === action.repo);
					}),
					updatedAt: new Date,
				}
			);
		}

		case REMOVE_STAGE: {
			const newState = {...state};
			newState.projects.forEach((project) => {
				if (project.owner === action.owner && project.repo === action.repo) {
					project.stages = project.stages.filter((stage) => {
						return stage.name !== action.stage;
					})
				}
			});
			return Object.assign(
				{},
				state,
				newState,
				{
					updatedAt: new Date,
				}
			);
		}
	}

	return state;
};

export default projectsReducer;
