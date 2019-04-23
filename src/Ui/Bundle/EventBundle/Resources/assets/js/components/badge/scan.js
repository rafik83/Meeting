import React, {Component, Fragment} from 'react';
import ReactDOM from 'react-dom';
import QrReader from 'react-qr-reader';

class Scan extends Component {
  constructor() {
    super();
    this.element = document.querySelector('#scan');
    this.state = {
      result: 'No result'
    }
  }

  handleScan(data) {
    if (data) {
      this.setState({
        result: data
      })
    }
  }

  handleError(err) {
    console.error(err)
  }

  render() {
    return (
      <Fragment>
        <QrReader
          delay={300}
          style={{width: '100%'}}
          onScan={this.handleScan}
          onError={this.handleError}
        />
        <p>{this.state.result}</p>
      </Fragment>
    );
  }
}

ReactDOM.render(
  React.createElement(Scan),
  document.getElementById('scan')
);
