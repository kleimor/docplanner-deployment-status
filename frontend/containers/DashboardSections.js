import React from "react"
import DashboardCard from "../containers/DashboardCard"
import {connect} from "react-redux"

class DashboardSections extends React.Component {
	render = () => {
		if (0 === this.props.projects.length) {
			return <div></div>;
		}

		const cards = this.props.projects.map((project) => (
			<DashboardCard {...project} project={project} />
		));

		return (
			<div className="card">
				<div className="card-header text-center bg-faded">
					<strong>{this.props.title}</strong>
				</div>
				<div className="card-block p-2">
					<div className="d-flex justify-content-start align-items-stretch flex-wrap">
						{cards}
					</div>
				</div>
			</div>
		);
	}
}

const mapStateToProps = (state, ownProps) => ({
	...ownProps
});

const mapDispatchToProps = (dispatch) => ({});

export default connect(mapStateToProps, mapDispatchToProps)(DashboardSections);
