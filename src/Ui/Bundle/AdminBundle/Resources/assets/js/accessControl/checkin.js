import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import moment from 'moment';
import db from '../vendor/db';
import Spool from './spool';
import dateDiffCalculator from './dateDiffCalculator';

class Checkin extends Component {
    constructor() {
        super();

        this.element = document.querySelector('#checkin');

        this.state = {
            participants: [],
            search: null,
            checkin: false,
            dateDiffInMilliSeconds: dateDiffCalculator(this.element.dataset.serverDate)
        };

        this.handleCheckin = this.handleCheckin.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.spool = new Spool(dateDiffCalculator(this.element.dataset.serverDate));
        this.spool.init();
    }

    handleSearch(e) {
        e.preventDefault();

        let search = this.state.search.toLowerCase();

        if (!search) {
            return;
        }

        db.table('identifiers').toArray().then(results => {
            let participants = results.filter(object => {
                return object.lastName.toLowerCase().indexOf(search) > -1;
            });

            this.setState({ participants, submitted: true });
        });
    }

    handleCheckin(identifier, index) {
        let participants = this.state.participants;

        db.table('identifiers').get(identifier).then(result => {
            if (result) {
                participants[index].checkin = new Date();
                this.spool.add(identifier);

                this.setState({ participants, checkin: true });
            }
        });
    }

    renderSearch() {
        return (
            <form onSubmit={this.handleSearch}>
                <div className={"col-lg-10"}>
                    <input
                        type={"text"}
                        className={"form-control"}
                        placeholder={this.element.dataset.searchByName}
                        name={"search"}
                        onChange={(e) => this.setState({ search: e.target.value })} />
                </div>
                <div className={"col-lg-2"}>
                    <button type={"submit"} className={"btn btn-primary"}>
                        {this.element.dataset.search}
                    </button>
                </div>
            </form>
        )
    }

    renderTable() {
        let { participants } = this.state;

        return (
            <div className={"col-lg-12"}>
                {participants.length > 0 &&
                    <table className={"table table-hover"}>
                        <thead>
                            <tr>
                                <th>{this.element.dataset.firstname}</th>
                                <th>{this.element.dataset.lastname}</th>
                                <th>{this.element.dataset.sheet}</th>
                                <th>{this.element.dataset.type}</th>
                                <th>{this.element.dataset.dateCheckin}</th>
                                <th className={"col-xs-1"}></th>
                                <th className={"col-xs-1"}></th>
                                <th className={"col-xs-1"}></th>
                            </tr>
                        </thead>
                        <tbody>
                        {participants.map((participant, index) => (
                            <tr key={participant.identifier}>
                                <td>{participant.firstName}</td>
                                <td>{participant.lastName}</td>
                                <td>{participant.sheetTitle}</td>
                                <td>{participant.participationType}</td>
                                <td>{participant.checkin && moment(participant.checkin.date).format('L HH:mm')}</td>
                                <td>
                                    {!participant.checkin &&
                                        <button
                                            className={'btn btn-success'}
                                            onClick={() => this.handleCheckin(participant.identifier, index)}>
                                            {this.element.dataset.checkin}
                                        </button>
                                    }
                                </td>
                                <td>
                                    {this.element.dataset.showPrintBadge === 'true' &&
                                        <a href={participant.badgeUrl} target={'_blank'}
                                           className={'btn btn-info'}>
                                            {this.element.dataset.printBadge}
                                        </a>
                                    }
                                </td>
                                <td>
                                    {this.element.dataset.showPrintBadge === 'true' &&
                                        <a href={participant.planningUrl} target={'_blank'}
                                           className={'btn btn-info'}>
                                            {this.element.dataset.printPlanning}
                                        </a>
                                    }
                                </td>
                            </tr>
                        ))}
                        </tbody>
                    </table>
                }
            </div>
        )
    }

    render() {
        let { participants, submitted } = this.state;

        return (
            <div className={"row"}>
                {participants.length === 0 && submitted &&
                    <div className={"col-lg-12"}>
                        <div className={"alert alert-danger"}>{this.element.dataset.noResult}</div>
                    </div>
                }
                {this.renderSearch()}
                {this.renderTable()}
            </div>
        );
    }
}

ReactDOM.render(
    React.createElement(Checkin),
    document.getElementById('checkin')
);
