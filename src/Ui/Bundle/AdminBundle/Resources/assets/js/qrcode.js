import React, { Component, Fragment } from 'react';
import ReactDOM from 'react-dom';
import QrReader from 'react-qr-reader';
import db from './vendor/db';

class QrCode extends Component {
    constructor() {
        super();

        this.state = {
            display: false,
            error: false,
            nbBadgeImported: 0,
            result: null
        };

        this.element = document.querySelector('#qrcode');

        this.handleScan = this.handleScan.bind(this);
        this.handleError = this.handleError.bind(this);
        this.handleReset = this.handleReset.bind(this);
    }

    componentDidMount() {
        let data = JSON.parse(this.element.dataset.payloads);

        db.table('qrCodePayloads').clear();
        db.table('qrCodePayloads').bulkAdd(data).then(() => {
            this.setState({ display: true });

            db.table('qrCodePayloads').count().then(count => {
                this.setState({ nbBadgeImported: count });
            });
        });
    }

    handleScan(payload) {
        if (payload) {
            db.table('qrCodePayloads').get(payload).then(result => {
                if (result) {
                    db.table('spool').add({ payload: result.payload, scannedAt: new Date() });
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
            <div className="row">
                <div className="col-md-12">
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

                            <button className={"btn btn-primary"} onClick={this.handleReset}>
                                {this.element.dataset.close}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    render() {
        let { display, error, result, nbBadgeImported} = this.state;

        return (
            <Fragment>
                {nbBadgeImported > 0 &&
                    <p>
                        {this.element.dataset.numberOfAvailableBadge} : {nbBadgeImported}
                    </p>
                }

                {display &&
                    <QrReader
                        delay={300}
                        facingMode={'user'}
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
