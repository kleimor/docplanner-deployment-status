import {combineReducers} from "redux";
import projectsReducer from "./projects"
import commitsReducer from "./commits"
import commitsDiffReducer from "./commits_diff"
import statusesReducer from "./statuses"
import starredReducer from "./starred"
import deploymentsReducer from "./deployments"

const rootReducer = combineReducers({
	projects: projectsReducer,
	commits: commitsReducer,
	commitsDiff: commitsDiffReducer,
	statuses: statusesReducer,
	starred: starredReducer,
	deployments: deploymentsReducer,
});

export default rootReducer;
