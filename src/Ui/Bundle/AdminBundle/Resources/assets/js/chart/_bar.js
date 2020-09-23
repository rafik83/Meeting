import Chart from 'chart.js';

function Bar(element) {
    this.element = element;
    this.legend = element.getAttribute('data-chart-legend');
    this.labels = JSON.parse(element.getAttribute('data-chart-label'));
    this.datas = JSON.parse(element.getAttribute('data-chart-data'));
    this.min = element.getAttribute('data-chart-axeY-min');
    this.max = element.getAttribute('data-chart-axeY-max');

    var axeYtickOptions = {
        beginAtZero: true
    };

    if (this.min !== null) {
        axeYtickOptions.min = JSON.parse(this.min);
    }

    if (this.max !== null) {
        axeYtickOptions.max = JSON.parse(this.max);
    }

    this.chart = new Chart(this.element, {
        type: 'bar',
        data: {
            'labels': this.labels,
            'datasets': [
                {
                    label: this.legend,
                    data: this.datas,
                    backgroundColor: "rgba(14,72,100,1)",
                    borderWidth: 1
                }
            ]
        },
        'options': {
            scales: {
                yAxes: [{
                    ticks: axeYtickOptions
                }]
            }
        }
    });
}

export default Bar;
