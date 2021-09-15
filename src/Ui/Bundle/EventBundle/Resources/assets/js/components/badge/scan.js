import React, {Component, Fragment} from 'react';
import ReactDOM from 'react-dom';
import QrReader from 'react-qr-reader';
import axios from 'axios';

class Scan extends Component {
  constructor() {
    super();
    this.element = document.querySelector('#scan');
    this.handleScanEndpoint = this.element.getAttribute('data-handle-scan-endpoint');
    this.loadingMessage = this.element.getAttribute('data-loading-message');

    this.state = {
      displayScan: true,
      isLoading: false,
    };

    this.handleScan = this.handleScan.bind(this);
    this.handleError = this.handleError.bind(this);
  }

  getScannedUserEventProfile(identifier) {
    this.setState({ ...this.state, isLoading: true });

    axios
      .post(this.handleScanEndpoint, { identifier })
      .then((result) => {
        document.location.href = result.data.url;
      })
      .catch((error) => {
        alert(error);
        this.setState({ ...this.state, displayScan: true });
      })
    ;
  }

  handleScan(identifier) {
    if (!identifier) {
      return;
    }

    this.setState({ ...this.state, displayScan: false });
    this.getScannedUserEventProfile(identifier);
  }

  handleError(error) {
    alert(error);
  }

  render() {
    const { displayScan, isLoading } = this.state;

    return (
      <Fragment>
        {displayScan && <QrReader
          delay={300}
          style={{width: '100%', height: '600px'}}
          onScan={this.handleScan}
          onError={this.handleError}
        />}

        {isLoading && <p>{this.loadingMessage}</p>}
      </Fragment>
    );
  }
}

ReactDOM.render(
  React.createElement(Scan),
  document.getElementById('scan')
);
