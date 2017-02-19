import React from "react";
import {fetchCommits} from "../actions/commits";
import {fetchStatuses} from "../actions/statuses";
import {toggleStarred} from "../actions/starred";
import {connect} from "react-redux";
import RelativeTime from "../components/RelativeTime";
import Stage from "./Stage";

class ProjectCard extends React.Component {
	componentDidMount () {
		this.loadProjectData();
		jQuery(this.refs.starButton).tooltip();
	}

	componentDidUpdate () {
		jQuery(this.refs.starButton).tooltip();
	}

	loadProjectData () {
		this.loadProjectCommits();
		this.loadProjectStatuses();
	}

	loadProjectCommits () {
		const {owner, repo, stages} = this.props;

		stages.forEach((stage) => {
			this.props.fetchCommits(owner, repo, stage.name);
		});
	}

	loadProjectStatuses () {
		const {owner, repo, stages} = this.props;

		stages.forEach((stage) => {
			this.props.fetchStatuses(owner, repo, stage.name);
		});
	}

	toggleStarred () {
		const {owner, repo} = this.props;

		this.props.toggleStarred(owner, repo);
	};

	getOverallState () {
		for (let stage in this.props.commits) {
			const stageCommits = this.props.commits[stage];
			if (null === stageCommits) {
				continue;
			}
			if (stageCommits.isLoading) {
				return "faded";
			} else if (false === stageCommits.isRecent) {
				return "danger"
			} else if (stageCommits.hasOwnProperty("commits")) {
				if (0 === stageCommits.commits.length) {
					return "danger";
				}
				// TODO: check whether commit on "stage" branch is latest from reference branch
			}
		}
		for (let stage in this.props.statuses) {
			const stageStatuses = this.props.statuses[stage];
			if (null === stageStatuses) {
				continue;
			}
			if (stageStatuses.isLoading) {
				return "";
			}
			else {
				if (false === stageStatuses.isRecent) {
					return "warning"
				}
				if (stageStatuses.statuses.length) {
					let latestStatusState = stageStatuses.statuses[0].state;
					if ("failure" === latestStatusState) {
						return "danger";
					}
				}
			}
		}

		return "success";
	}

	render = () => {
		const stages = this.props.stages.map((stage) => (
			<Stage
				stage={stage}
				commits={this.props.commits[stage.name]}
				statuses={this.props.statuses[stage.name]}
			></Stage>
		));

		let maxDate;
		for (let stage in this.props.commits) {
			const stageUpdatedAt = this.props.commits[stage].updatedAt;
			maxDate = ("undefined" === typeof maxDate || stageUpdatedAt > maxDate) ? stageUpdatedAt : maxDate;
		}

		const overallState = this.getOverallState();

		return (
			<div className={`col-sm-6 col-md-6 col-lg-4 col-xl-3 p-0`}>
				<div className={`card card-outline-${overallState} mx-2 mb-2`}>
					<div className={`card-header text-center bg-${overallState}`}>
						<a
							className={"faded" === overallState ? "text-primary" : "text-white"}
							href={`https://github.com/${this.props.owner}/${this.props.repo}`}
						>
							<strong>{this.props.owner}/{this.props.repo}</strong>
						</a>
					</div>
					<div className="card-block p-0">
						<ul className="list-group list-group-flush">
							{stages}
						</ul>
					</div>
					<div className="card-footer text-muted text-right">
						<div className="float-left">
							<button
								ref="starButton"
								className="btn btn-link btn-sm p-0 mr-1"
								onClick={this.toggleStarred.bind(this)}
								data-toggle="tooltip"
								data-placement="right"
								data-html="true"
								title={`<small>${this.props.isStarred ? "Unstar project" : "Star project"}</small>`}
							>
								<span
									className={`md-icon ${this.props.isStarred ? "text-warning" : "text-muted"}`}>star</span>
							</button>
						</div>
						<small className="float-right font-italic">
							<RelativeTime
								date={maxDate}
								onClick={this.loadProjectData.bind(this)}
							>
								<span className="md-icon pr-1">schedule</span>
							</RelativeTime>
						</small>
					</div>
				</div>
			</div>
		)
	}
}

const mapStateToProps = (state, ownProps) => {
	let commits = {};
	ownProps.stages.forEach((stage) => {
		let key = `${ownProps.owner}/${ownProps.repo}/${stage.name}`;
		commits[stage.name] = state.commits.forProject.hasOwnProperty(key) ?
			state.commits.forProject[key]
			:
			[];
	});
	let statuses = {};
	ownProps.stages.forEach((stage) => {
		let key = `${ownProps.owner}/${ownProps.repo}/${stage.name}`;
		statuses[stage.name] = state.statuses.forProject.hasOwnProperty(key) ?
			state.statuses.forProject[key]
			:
			null;
	});

	return {
		...ownProps,
		commits: commits,
		statuses: statuses,
		isStarred: state.starred.starred.indexOf(`${ownProps.owner}/${ownProps.repo}`) > -1,
	};
};

const mapDispatchToProps = (dispatch) => ({
	fetchCommits: (owner, repo, stage) => dispatch(fetchCommits(owner, repo, stage)),
	fetchStatuses: (owner, repo, stage) => dispatch(fetchStatuses(owner, repo, stage)),
	toggleStarred: (owner, repo) => dispatch(toggleStarred(owner, repo)),
});

export default connect(mapStateToProps, mapDispatchToProps)(ProjectCard);
