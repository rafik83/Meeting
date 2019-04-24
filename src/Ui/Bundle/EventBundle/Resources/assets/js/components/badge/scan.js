import React, {Component, Fragment} from 'react';
import ReactDOM from 'react-dom';
import QrReader from 'react-qr-reader';
import axios from 'axios';

class Scan extends Component {
  constructor() {
    super();
    this.element = document.querySelector('#scan');
    this.handleScanEndpoint = this.element.getAttribute('data-handle-scan-endpoint');

    this.state = {
      displayScan: true,
      isLoading: false,
      result: null
    };

    this.handleScan = this.handleScan.bind(this);
    this.handleError = this.handleError.bind(this);
  }

  getScannedUserEventProfile(identifier) {
    this.setState({ ...this.state, isLoading: true });

    axios
      .post(this.handleScanEndpoint, { identifier })
      .then((result) => {
        console.log(result);
        this.setState({ ...this.state, isLoading: false, result: result.data });
      })
      .catch((error) => console.error(error))
    ;
  }

  handleScan(identifier) {
    if (!identifier) {
      return;
    }

    this.setState({ ...this.state, displayScan: false });
    this.getScannedUserEventProfile(identifier);
  }

  handleError(err) {
    console.error(err)
  }

  render() {
    const { displayScan, isLoading, result } = this.state;

    return (
      <Fragment>
        {displayScan && <QrReader
          delay={300}
          style={{width: '100%'}}
          onScan={this.handleScan}
          onError={this.handleError}
        />}

        {isLoading && <div>Loading...</div>}

        {result && <div className="user clearfix">
          <div className="user__avatar">
            <span className="bullet"></span>
            <div className="avatar">
              {/*<img id="{{ participantAvatarId }}"*/}
              {/*     src="{{ participant.avatar|imagine_filter('user_avatar') }}"*/}
              {/*     alt="{{ participant.initials }} avatar">*/}
              {/*  <p id="{{ participantAvatarId }}">{{ participant.initials }}</p>*/}
            </div>
          </div>
          <div className="user__infos">
            <p className="name">{result.firstName} {result.lastName}</p>
            <p className="job">{''}</p>
          </div>
        </div>}
      </Fragment>
    );
  }
}

ReactDOM.render(
  React.createElement(Scan),
  document.getElementById('scan')
);
