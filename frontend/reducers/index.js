import {combineReducers} from "redux";
import projectsReducer from "./projects"
import commitsReducer from "./commits"
import commitsDiffReducer from "./commits_diff"
import statusesReducer from "./statuses"
import starredReducer from "./starred"
import deploymentsReducer from "./deployments"
import hooksReducer from "./hooks"

const rootReducer = combineReducers({
	projects: projectsReducer,
	commits: commitsReducer,
	commitsDiff: commitsDiffReducer,
	statuses: statusesReducer,
	starred: starredReducer,
	deployments: deploymentsReducer,
	hooks: hooksReducer,
});

export default rootReducer;
