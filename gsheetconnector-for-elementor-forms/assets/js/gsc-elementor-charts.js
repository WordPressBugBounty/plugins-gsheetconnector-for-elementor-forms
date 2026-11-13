(function () {
  "use strict";

  var lineChart = null;
  var pieChart = null;
  var chartI18n = {};
  var lastLineData = { labels: [], values: [] };

  function byId(id) {
    return document.getElementById(id);
  }

  if (typeof Chart !== "undefined") {
    // Draws the total in the middle of the doughnut.
    Chart.register({
      id: "gselefDoughnutCenterText",
      afterDraw: function (chart) {
        if ("doughnut" !== chart.config.type) {
          return;
        }
        var dataset = chart.data.datasets[0] || {};
        var total = (dataset.data || []).reduce(function (sum, value) {
          return sum + (Number(value) || 0);
        }, 0);
        var area = chart.chartArea;
        var centerX = (area.left + area.right) / 2;
        var centerY = (area.top + area.bottom) / 2;
        var ctx = chart.ctx;

        ctx.save();
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.font = "700 26px sans-serif";
        ctx.fillStyle = "#111827";
        ctx.fillText(total.toLocaleString(), centerX, centerY - 10);
        ctx.font = "400 12px sans-serif";
        ctx.fillStyle = "#6b7280";
        ctx.fillText(chartI18n.total || "Total", centerX, centerY + 14);
        ctx.restore();
      },
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initCharts();
    initDashboardFilter();
  });

  function hideInitialChartLoaders() {
    ["gselef-line-chart-loading", "gselef-pie-chart-loading"].forEach(function (id) {
      var loader = byId(id);
      if (loader) {
        loader.classList.add("is-hidden");
      }
    });
  }

  function initCharts() {
    var dataEl = byId("gselef-dashboard-chart-data");
    if (!dataEl || typeof Chart === "undefined") {
      hideInitialChartLoaders();
      return;
    }
    var data;
    try {
      data = JSON.parse(dataEl.textContent);
    } catch (e) {
      hideInitialChartLoaders();
      return;
    }
    chartI18n = data.i18n || {};
    renderCharts(data);
    hideInitialChartLoaders();
  }

  function renderCharts(data) {
    lastLineData = { labels: data.labels || [], values: data.values || [] };
    drawLineChart();

    var pieCanvas = byId("gselef-entries-pie-chart");
    if (!pieCanvas) {
      return;
    }

    var read = Number(data.read) || 0;
    var unread = Number(data.unread) || 0;
    var pieLabels = [
      (chartI18n.read || "Read") + " (" + read + ")",
      (chartI18n.unread || "Unread") + " (" + unread + ")",
    ];

    if (pieChart) {
      pieChart.data.labels = pieLabels;
      pieChart.data.datasets[0].data = [read, unread];
      pieChart.update();
      return;
    }

    pieChart = new Chart(pieCanvas.getContext("2d"), {
      type: "doughnut",
      data: {
        labels: pieLabels,
        datasets: [
          {
            data: [read, unread],
            backgroundColor: ["#10b981", "#f59e0b"],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: { legend: { position: "bottom" } },
      },
    });
  }

  function drawLineChart() {
    var lineCanvas = byId("gselef-entries-line-chart");
    if (!lineCanvas) {
      return;
    }
    if (lineChart) {
      lineChart.destroy();
    }

    lineChart = new Chart(lineCanvas.getContext("2d"), {
      type: "line",
      data: {
        labels: lastLineData.labels,
        datasets: [
          {
            label: chartI18n.entries || "Entries",
            data: lastLineData.values,
            borderColor: "#6d28d9",
            backgroundColor: "rgba(109, 40, 217, 0.12)",
            fill: true,
            tension: 0.35,
            pointRadius: 2,
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { offset: true },
          y: { beginAtZero: true, ticks: { precision: 0 } },
        },
      },
    });
  }

  function post(body) {
    return fetch(window.ajaxurl || "admin-ajax.php", {
      method: "POST",
      body: body,
      credentials: "same-origin",
    }).then(function (res) {
      return res.json();
    });
  }

  /**
   * The Form filter re-fetches the stat counts and chart data and refreshes
   * the cards and both charts in place.
   */
  function initDashboardFilter() {
    var select = byId("gselef-entries-filter-form");
    var nonceField = byId("gselef-dashboard-stats-nonce");
    var loader = byId("gselef-entries-loader");
    if (!select || !nonceField) {
      return;
    }

    var statEls = {
      total: byId("gselef-stat-total"),
      unread: byId("gselef-stat-unread"),
      read: byId("gselef-stat-read"),
      new_today: byId("gselef-stat-new-today"),
    };

    select.addEventListener("change", function () {
      if (loader) {
        loader.classList.remove("d-none");
      }

      var formData = new FormData();
      formData.append("action", "gselef_dashboard_stats_query");
      formData.append("security", nonceField.value);
      formData.append("form", select.value);
      formData.append("period", "30days");

      post(formData)
        .then(function (json) {
          if (!json || !json.success) {
            return;
          }
          var data = json.data;
          Object.keys(statEls).forEach(function (key) {
            if (statEls[key]) {
              statEls[key].textContent = Number(data[key]).toLocaleString();
            }
          });
          renderCharts(data);
        })
        .catch(function () {})
        .then(function () {
          if (loader) {
            loader.classList.add("d-none");
          }
        });
    });
  }
})();
