import React from 'react';
import {IndexLink, Link} from 'react-router';
import {connect} from "react-redux";
import {fetchProjects} from "../actions/projects";
import {reloadProjectData} from "../helpers/utilities";

class App extends React.Component {
	componentDidMount () {
		this.props.fetchProjects();
	}

	clearCacheAndReloadProjects () {
		this.props.projects.projects.forEach((project) => {
			reloadProjectData(project.owner, project.repo);
		})
	}

	render = () => {
		return <div>
			<nav className="navbar navbar-toggleable-md navbar-inverse bg-inverse">
				<button className="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
						data-target="#navbar-top" aria-controls="navbar-top" aria-expanded="false"
						aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<IndexLink className="navbar-brand" to="/">
					Github deployment tracker
				</IndexLink>

				<div className="collapse navbar-collapse" id="navbar-top">
					<ul className="navbar-nav mr-auto">
						<li className="nav-item">
							<IndexLink to="/" className="nav-link" activeClassName="active">
								Dashboard
							</IndexLink>
						</li>
						{/*<li className="nav-item">*/}
							{/*<Link to="/detailed" className="nav-link" activeClassName="active">*/}
								{/*Detailed*/}
							{/*</Link>*/}
						{/*</li>*/}
					</ul>
					<div className="btn-group btn-group-sm">
						<button className="btn btn-outline-secondary" onClick={this.clearCacheAndReloadProjects.bind(this)}>
							Reload all projects
						</button>
					</div>
				</div>
			</nav>

			<div className="container-fluid">
				{this.props.children}
			</div>
		</div>
	}
}

const mapStateToProps = (state) => ({
	...state
});

const mapDispatchToProps = (dispatch) => ({
	fetchProjects: () => dispatch(fetchProjects())
});

export default connect(mapStateToProps, mapDispatchToProps)(App);
