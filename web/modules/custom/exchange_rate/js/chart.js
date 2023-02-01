(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.myChart = {
    attach: function (context, settings) {
      if (context !== document) return;
      this.Chart();
    },Chart: function () {
      jQuery("document").ready(function ($) {
        const ctx = document.getElementById('myChart');
        let arrayCurrencies = drupalSettings.exchange_rate.currency;

        let exchangedate = [];
        let rate = [];
        let nameCurrency = [];
        let Currencies = [];

        for(let i = 0; i < arrayCurrencies.length; i++) {
          exchangedate = [];
          rate = [];
          nameCurrency = [];

          for (let j = 0; j < arrayCurrencies[i].length; j++) {
            exchangedate.push(arrayCurrencies[i][j].exchangedate);
            rate.push(arrayCurrencies[i][j].rate);
            nameCurrency[0] = arrayCurrencies[i][j].cc;
          }

          Currencies.push([nameCurrency, rate, exchangedate]);
        }

        let objectCurrensies = [];
        color = ['red', 'blue', 'gray', 'black', 'purple', 'yellow', 'orange'];

        for (let i = 0; i < Currencies.length; i++) {
          let dataFirst = {
            label: Currencies[i][0],
            data: Currencies[i][1].reverse(),
            lineTension: 0,
            fill: false,
            borderColor: color[i]
          };

          objectCurrensies[i] = dataFirst;
        }

        let speedData = {
          labels: Currencies[0][2].reverse(),
          datasets: objectCurrensies
        };

        let chartOptions = {
          legend: {
            display: true,
            position: 'top',
            labels: {
              boxWidth: 80,
              fontColor: 'black'
            }
          }
        };

        let lineChart = new Chart(ctx, {
          type: 'line',
          data: speedData,
          options: chartOptions
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
