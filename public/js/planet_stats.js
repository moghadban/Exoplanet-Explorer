// planet_stats.js (Dark Background Optimized)
document.addEventListener('DOMContentLoaded', () => {
    const typeColors = {
        "Gas giant": "#b39f4f",       // deep gold
        "Neptune-like": "#2468ac",    // strong ocean blue
        "Super-Earth": "#1e7139",     // rich green
        "Terrestrial": "#8e1b1b",     // vibrant red
        "Unknown": "#757473"
    };
    const fallbackColor = "#232323";

    // Universal dark-theme styles
    const textColor = "#f5f5f5";
    const gridColor = "rgba(255, 255, 255, 0.2)";
    const axisBorderColor = "#e0e0e0";
    const tooltipBg = "rgba(40, 40, 40, 0.95)";
    const tooltipBorder = "#aaaaaa";
    const legendLabelColor = "#ffffff";

    // -----------------------------
    // Helper: normalize label
    // -----------------------------
    function normalizeLabel(label) {
        return label.trim();
    }

    // -----------------------------
    // Helper: get color with hover effect
    // -----------------------------
    function getColor(label) {
        const hex = typeColors[normalizeLabel(label)] || fallbackColor;
        return hex;
    }

    function getHoverColor(label) {
        const hex = getColor(label);
        const amt = 30;
        let col = hex.replace(/^#/, '');
        let num = parseInt(col, 16);
        let r = Math.min((num >> 16) + amt, 255);
        let g = Math.min(((num >> 8) & 0x00FF) + amt, 255);
        let b = Math.min((num & 0x0000FF) + amt, 255);
        return `rgb(${r},${g},${b})`;
    }

    // -----------------------------
    // Pie Chart for Planet Type Distribution
    // -----------------------------
    const typePieCanvas = document.getElementById('typePie');
    if (typePieCanvas) {
        if (window.typePieChart) window.typePieChart.destroy();

        const typeLabels = JSON.parse(typePieCanvas.dataset.labels).map(normalizeLabel);
        const typeValues = JSON.parse(typePieCanvas.dataset.values);

        window.typePieChart = new Chart(typePieCanvas, {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: typeLabels.map(getColor),
                    hoverBackgroundColor: typeLabels.map(getHoverColor),
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverBorderColor: '#ffffcc',
                    hoverBorderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: legendLabelColor,
                            boxWidth: 20,
                            padding: 10
                        }
                    },
                    tooltip: {
                        backgroundColor: tooltipBg,
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: tooltipBorder,
                        borderWidth: 1,
                        callbacks: {
                            label: function (context) {
                                const value = context.parsed ?? 0;
                                const total = context.chart._metasets[0].total ?? 0;
                                const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                elements: {
                    arc: {
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }
                }
            }
        });
    }

    // -----------------------------
    // Stacked Bar Chart for Planets by Year
    // -----------------------------
    const byYearCanvas = document.getElementById('byYearStacked');
    if (byYearCanvas) {
        if (window.byYearChart) window.byYearChart.destroy();

        const yearLabels = JSON.parse(byYearCanvas.dataset.yearlabels);
        const yearTypes = JSON.parse(byYearCanvas.dataset.types).map(normalizeLabel);
        const yearDatasetsRaw = JSON.parse(byYearCanvas.dataset.datasets);

        const datasets = yearTypes.map((type, idx) => ({
            label: type,
            data: yearDatasetsRaw.map(row => row[idx] ?? 0),
            backgroundColor: getColor(type),
            hoverBackgroundColor: getHoverColor(type),
            borderColor: '#ffffff',
            borderWidth: 2
        }));

        window.byYearChart = new Chart(byYearCanvas, {
            type: 'bar',
            data: {
                labels: yearLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {color: legendLabelColor}
                    },
                    tooltip: {
                        backgroundColor: tooltipBg,
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: tooltipBorder,
                        borderWidth: 1,
                        callbacks: {
                            label: function (context) {
                                return `${context.dataset.label}: ${context.parsed.y ?? 0}`;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Discovery Year',
                            color: textColor
                        },
                        ticks: {color: textColor},
                        grid: {
                            color: gridColor,
                            borderColor: axisBorderColor
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Planets',
                            color: textColor
                        },
                        ticks: {color: textColor},
                        grid: {
                            color: gridColor,
                            borderColor: axisBorderColor
                        }
                    }
                }
            }
        });

        // -----------------------------
        // Export / Download Buttons
        // -----------------------------
        const exportContainer = document.getElementById('chartExports');
        if (exportContainer) {
            exportContainer.innerHTML = '';

            const styleBtn = (btn) => {
                btn.style.backgroundColor = '#2563eb';
                btn.style.color = '#fff';
                btn.style.border = '1px solid #aaa';
                btn.style.borderRadius = '5px';
                btn.style.padding = '6px 12px';
                btn.style.marginRight = '6px';
                btn.style.cursor = 'pointer';
                btn.style.fontSize = '0.9rem';
                btn.style.transition = 'background-color 0.3s ease';
                btn.onmouseover = () => btn.style.backgroundColor = '#3b82f6';
                btn.onmouseout = () => btn.style.backgroundColor = '#2563eb';
            };

            // Pie Chart PNG
            if (window.typePieChart) {
                const piePngBtn = document.createElement('button');
                piePngBtn.textContent = 'Download Pie PNG';
                styleBtn(piePngBtn);
                piePngBtn.onclick = () => {
                    const link = document.createElement('a');
                    link.href = typePieCanvas.toDataURL('image/png');
                    link.download = 'planet_type_distribution.png';
                    link.click();
                };
                exportContainer.appendChild(piePngBtn);
            }

            // Bar Chart PNG
            const barPngBtn = document.createElement('button');
            barPngBtn.textContent = 'Download Bar PNG';
            styleBtn(barPngBtn);
            barPngBtn.onclick = () => {
                const link = document.createElement('a');
                link.href = byYearCanvas.toDataURL('image/png');
                link.download = 'planet_stats.png';
                link.click();
            };
            exportContainer.appendChild(barPngBtn);

            // Bar Chart CSV
            const csvBtn = document.createElement('button');
            csvBtn.textContent = 'Download CSV';
            styleBtn(csvBtn);
            csvBtn.onclick = () => {
                let csv = 'Year,' + yearTypes.join(',') + '\n';
                yearLabels.forEach((year, i) => {
                    const row = [year];
                    yearTypes.forEach((type, idx) => row.push(yearDatasetsRaw[i][idx] ?? 0));
                    csv += row.join(',') + '\n';
                });
                const blob = new Blob([csv], {type: 'text/csv'});
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'planet_stats.csv';
                link.click();
            };
            exportContainer.appendChild(csvBtn);
        }
    }
});
