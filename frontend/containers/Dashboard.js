import React from "react";
import DashboardSections from "../containers/DashboardSections";
import {connect} from "react-redux"

class Dashboard extends React.Component {
	render = () => {
		if (this.props.isLoading) {
			return (
				<div className="display-1 text-center">
					<span className="md-icon rotating">sync</span>
				</div>
			);
		}
		return (
			<div>
				<div className="row">
					<div className="col-md-12 pt-3">
						<DashboardSections title="Starred" projects={this.props.starredProjects}/>
					</div>
				</div>
				<div className="row">
					<div className="col-md-12 pt-3">
						<DashboardSections title="Projects" projects={this.props.otherProjects}/>
					</div>
				</div>
			</div>
		)
	}
}

const mapStateToProps = (state, ownProps) => ({
	...ownProps,
	isLoading: state.projects.isLoading,
	starredProjects: state.projects.projects.filter((project) => {
		return state.starred.starred.indexOf(`${project.owner}/${project.repo}`) > -1;
	}),
	otherProjects: state.projects.projects.filter((project) => {
		return -1 === state.starred.starred.indexOf(`${project.owner}/${project.repo}`);
	}),
});

const mapDispatchToProps = (dispatch) => ({});

export default connect(mapStateToProps, mapDispatchToProps)(Dashboard);
