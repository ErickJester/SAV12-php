(function () {
  'use strict';

  if (typeof window === 'undefined' || typeof window.Chart === 'undefined') {
    return;
  }

  var rawData = window.__reportesData;
  if (!rawData || typeof rawData !== 'object') {
    return;
  }

  function toNumber(value) {
    var num = Number(value);
    return Number.isFinite(num) ? num : null;
  }

  function pick(obj, keys) {
    for (var i = 0; i < keys.length; i += 1) {
      var v = obj[keys[i]];
      if (v !== undefined && v !== null && String(v).trim() !== '') {
        return v;
      }
    }
    return null;
  }

  function normalizeDataset(input, labelKeys, valueKeys) {
    var labels = [];
    var values = [];

    if (!input) {
      return { labels: labels, values: values };
    }

    if (Array.isArray(input)) {
      for (var i = 0; i < input.length; i += 1) {
        var item = input[i];
        if (!item || typeof item !== 'object') {
          continue;
        }

        var rawLabel = pick(item, labelKeys);
        var rawValue = pick(item, valueKeys);
        var val = toNumber(rawValue);

        if (rawLabel === null || val === null) {
          continue;
        }

        labels.push(String(rawLabel));
        values.push(val);
      }

      return { labels: labels, values: values };
    }

    if (typeof input === 'object') {
      var keys = Object.keys(input);
      for (var k = 0; k < keys.length; k += 1) {
        var key = keys[k];
        var value = input[key];
        var num = toNumber(value);

        if (num === null) {
          continue;
        }

        labels.push(String(key));
        values.push(num);
      }
    }

    return { labels: labels, values: values };
  }

  function renderChart(canvasId, title, dataset, type) {
    if (!dataset || !dataset.labels.length || !dataset.values.length) {
      return;
    }

    var canvas = document.getElementById(canvasId);
    if (!canvas) {
      return;
    }

    var ctx = canvas.getContext('2d');
    if (!ctx) {
      return;
    }

    new window.Chart(ctx, {
      type: type || 'bar',
      data: {
        labels: dataset.labels,
        datasets: [{
          label: title,
          data: dataset.values,
          backgroundColor: [
            'rgba(37, 99, 235, 0.70)',
            'rgba(16, 185, 129, 0.70)',
            'rgba(245, 158, 11, 0.70)',
            'rgba(239, 68, 68, 0.70)',
            'rgba(124, 58, 237, 0.70)',
            'rgba(6, 182, 212, 0.70)'
          ],
          borderColor: [
            'rgba(37, 99, 235, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(245, 158, 11, 1)',
            'rgba(239, 68, 68, 1)',
            'rgba(124, 58, 237, 1)',
            'rgba(6, 182, 212, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: type === 'bar' ? {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        } : undefined,
        plugins: {
          legend: {
            display: type !== 'bar'
          }
        }
      }
    });
  }

  var prioridadData = normalizeDataset(
    rawData.prioridad,
    ['prioridad', 'nombre', 'label', 'categoria', 'estado', 'tipo'],
    ['total', 'cantidad', 'value', 'valor', 'conteo', 'count']
  );

  var ubicacionesData = normalizeDataset(
    rawData.ubicaciones,
    ['ubicacion', 'nombre', 'label', 'sede', 'area', 'categoria'],
    ['total', 'cantidad', 'value', 'valor', 'conteo', 'count']
  );

  renderChart('chartPrioridad', 'Tickets por prioridad', prioridadData, 'bar');
  renderChart('chartUbicaciones', 'Tickets por ubicación', ubicacionesData, 'pie');
})();
