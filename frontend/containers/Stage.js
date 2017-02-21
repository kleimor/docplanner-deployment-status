import React from "react";
import {connect} from "react-redux";
import jQuery from "jquery";
import {fetchCommitsDiff} from "../actions/commits_diff";

class Stage extends React.Component {
	componentDidMount () {
		jQuery(this.refs.commitBadge).tooltip();
		jQuery(this.refs.statusBadge).tooltip();
	}

	componentDidUpdate () {
		jQuery(this.refs.commitBadge).tooltip();
		jQuery(this.refs.statusBadge).tooltip();
	}

	formatMessage (message) {
		let formatterMessage = jQuery("<div>").text(message).html();
		return formatterMessage.replace(/([^>\r\n]?)((?:\r\n|\n\r|\r|\n)+)/g, '$1<br ' + '/>$2');
	}

	render = () => {
		const {stage, commits, commitsDiff, statuses, deployments} = this.props;

		let commitHtml = "";
		if (commits.isLoading) {
			commitHtml = (
				<span className="md-icon text-warning">more_horiz</span>
			);
		} else {
			if (commits.commits && commits.commits.length) {
				const latestCommit = commits.commits[0];
				let commitText = latestCommit.sha.substr(0, 6);

				let commitClassNames = ["badge"];
				if (commitsDiff && commitsDiff.diff) {
					const aheadCount = parseInt(commitsDiff.diff.ahead_by);
					if (aheadCount) {
						commitText = `${commitText} (-${aheadCount})`;
						commitClassNames.push("badge-warning");
					} else {
						commitClassNames.push("badge-success");
					}
				} else {
					commitClassNames.push("badge-default");
				}

				commitHtml = (
					<a href={commits.commits[0].html_url}>
					<span
						ref="commitBadge"
						className={commitClassNames.join(" ")}
						data-toggle="tooltip"
						data-placement="top"
						data-html="true"
						title={`
							<small class="text-left">
								${this.formatMessage(latestCommit.commit.message)}
								<br />
								${this.formatMessage(latestCommit.author.login)}
							</small>
						`}
					>
						{commitText}
					</span>
					</a>
				);
			} else {
				commitHtml = (
					<span ref="commitBadge" className="md-icon text-danger">error_outline</span>
				);
			}
		}

		let statusHtml = <span className="md-icon text-danger">more_horiz</span>;
		if (statuses) {
			if (statuses.isLoading) {
				statusHtml = <span className="md-icon text-warning">more_horiz</span>;
			} else {
				const latestStatus = statuses.statuses.length ?
					statuses.statuses[0]
					:
					null
				;
				if (latestStatus) {
					statusHtml = (
						<a href={latestStatus.target_url} title={latestStatus.description} target="_blank">
							<span
								ref="statusBadge"
								className={{
									failure: "md-icon text-danger",
									pending: "md-icon rotating text-warning",
									success: "md-icon text-success",
								}[latestStatus.state]}
								data-toggle="tooltip"
								data-placement="top"
								data-html="true"
								title={`<small>${latestStatus.description}<br />${latestStatus.context}</small>`}
							>
								{{
									failure: "error_outline",
									pending: <span className="rotating font-weight-bold">sync</span>,
									success: <span className="font-weight-bold">done</span>,
								}[latestStatus.state]}
							</span>
						</a>
					);
				} else {
					statusHtml = "";
				}
			}
		}

		let deploymentHtml = <span className="md-icon text-warning">more_horiz</span>;
		if (deployments && !deployments.isLoading) {
			if (deployments.isLoading) {
				deploymentHtml = <span className="md-icon text-warning">more_horiz</span>;
			} else {
				const latestDeployment = deployments.latestDeployment;
				if (latestDeployment && latestDeployment.statuses) {
					const latestDeploymentStatus = latestDeployment.statuses[0];
					switch (latestDeploymentStatus.state) {
						case "failure":
						case "error":
							deploymentHtml = <span className="md-icon text-danger font-weight-bold">sync_problem</span>;
							break;

						case "pending":
							deploymentHtml = <span className="md-icon rotating text-warning">sync</span>;
							break;

						case "success":
							deploymentHtml = <span className="md-icon text-success font-weight-bold">done</span>;
							break;
					}
				} else {
					deploymentHtml = <span className="md-icon text-muted font-weight-bold">sync_disabled</span>;
				}
			}
		}

		return (
			<li className="list-group-item">
				<div className="container-fluid w-100">
					<div className="row">
						<div className="col-6 p-0 text-left">{stage.name}</div>
						<div className="col-3 p-0 text-center">{commitHtml}</div>
						<div className="col-2 p-0 text-center">{statusHtml}</div>
						<div className="col-1 p-0 text-center">{deploymentHtml}</div>
					</div>
				</div>
			</li>
		);
	}
}

const mapStateToProps = (state, ownProps) => {
	return {
		...ownProps
	};
};

const mapDispatchToProps = (dispatch) => ({});

export default connect(mapStateToProps, mapDispatchToProps)(Stage);
