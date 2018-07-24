import React from 'react';
import ReactDOM from 'react-dom';
import QrReader from 'react-qr-reader';

class QrCode extends React.Component {
    handleScan(data) {
        if (data) {
            alert(data);
        }
    }

    handleError(error) {
        if (error) {
            alert(error);
        }
    }

    render() {
        return (
            <React.Fragment>
                <QrReader
                    delay={300}
                    onError={this.handleError}
                    onScan={this.handleScan}
                    style={{ width: '30%' }} />
            </React.Fragment>
        );
    }
}

ReactDOM.render(
    React.createElement(QrCode),
    document.getElementById('qrcode')
);
