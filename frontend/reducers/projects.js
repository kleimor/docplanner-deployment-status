import {FETCH_PROJECTS_STARTED, FETCH_PROJECTS_FINISHED} from "../actions/projects"

const initialState = {
	projects: [],
	isLoading: false,
};

const projectsReducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_PROJECTS_STARTED:
			return Object.assign(
				{},
				state,
				{
					isLoading: true,
					updatedAt: new Date,
				}
			);
			break;

		case FETCH_PROJECTS_FINISHED:
			return Object.assign(
				{},
				state,
				{
					projects: action.projects,
					isLoading: false,
					updatedAt: new Date,
				}
			)
				;
			break;
	}

	return state;
};

export default projectsReducer;
