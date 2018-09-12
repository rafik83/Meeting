import React, { Component, Fragment } from 'react';
import ReactDOM from 'react-dom';
import QrReader from 'react-qr-reader';
import db from '../vendor/db';
import Spool from './spool';

class QrCode extends Component {
    constructor() {
        super();

        this.state = {
            display: false,
            error: false,
            nbImportedIdentifiers: 0,
            result: null
        };

        this.element = document.querySelector('#qrcode');

        this.handleScan = this.handleScan.bind(this);
        this.handleError = this.handleError.bind(this);
        this.handleReset = this.handleReset.bind(this);
    }

    componentDidMount() {
        let data = JSON.parse(this.element.dataset.identifiers);

        db.table('identifiers').clear();
        db.table('identifiers').bulkAdd(Object.values(data)).then(() => {
            this.setState({ display: true });

            db.table('identifiers').count().then(count => {
                this.setState({ nbImportedIdentifiers: count });
            });
        });
    }

    handleScan(identifier) {
        if (identifier) {
            let spool = new Spool();

            db.table('identifiers').get(identifier).then(result => {
                if (result) {
                    spool.add(identifier);
                    this.setState({ display: false, error: false, result: result });
                } else {
                    this.setState({ display: false, error: true, result: null });
                }
            });
        }
    }

    handleError(error) {
        if (error) {
            alert(error);
        }
    }

    handleReset() {
        this.setState({ display: true, error: false, result: null });
    }

    renderResult(result) {
        return (
            <div>
                <div className="panel panel-default">
                    <div className="panel-heading">
                        <i className="glyphicon glyphicon-qrcode"></i> {this.element.dataset.title}
                    </div>
                    <div className="panel-body text-center h1 dashboard-total-orders">
                        {result &&
                            <div>
                                {result.firstName} {result.lastName}
                                <br/>
                                {result.sheetTitle}
                            </div>
                        }

                        {!result && <div className={'alert alert-danger'}>{this.element.dataset.notFound}</div>}

                        {result &&
                            <a className={"btn btn-default btn-lg"} href={result.badgeUrl} target={"_blank"}>
                                {this.element.dataset.printBadge}
                            </a>
                        }
                        <a className={"btn btn-primary btn-lg"} onClick={this.handleReset}>
                            {this.element.dataset.close}
                        </a>
                    </div>
                </div>
            </div>
        );
    }

    render() {
        let { display, error, result, nbImportedIdentifiers} = this.state;

        return (
            <Fragment>
                {nbImportedIdentifiers > 0 &&
                    <p>
                        {this.element.dataset.numberOfAvailableIdentifiers} : {nbImportedIdentifiers}
                    </p>
                }

                {display &&
                    <QrReader
                        delay={300}
                        onScan={this.handleScan}
                        onError={this.handleError} />
                }

                {error && this.renderResult(null)}
                {result && this.renderResult(result)}
            </Fragment>
        );
    }
}

ReactDOM.render(
    React.createElement(QrCode),
    document.getElementById('qrcode')
);
