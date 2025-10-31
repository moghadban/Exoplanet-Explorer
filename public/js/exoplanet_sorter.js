document.addEventListener("DOMContentLoaded", async () => {
    const criterionSelect = document.getElementById("criterion");
    const orderSelect = document.getElementById("order");
    const canvas = document.getElementById("sortedChart");
    const loadingIndicator = document.getElementById("loadingIndicator");

    // Selecting the pre-existing div from the Twig file for the table container.
    const tableContainer = document.getElementById('exoplanetTableContainer');

    if (!canvas || !loadingIndicator || !tableContainer) {
        console.error("Required elements (canvas, loading indicator, or table container) not found.");
        return;
    }

    const ctx = canvas.getContext("2d");
    let chart = null;

    /**
     * Toggles the visibility of the loading indicator and the chart canvas.
     * @param {boolean} isLoading - true to show loader/hide chart, false to hide loader/show chart.
     */
    function setLoading(isLoading) {
        if (isLoading) {
            // Show the loading indicator
            loadingIndicator.classList.remove("d-none");
            // Hide the canvas and table
            canvas.classList.add("d-none");
            tableContainer.classList.add("d-none");
        } else {
            // Hide the loading indicator
            loadingIndicator.classList.add("d-none");
            // Show the canvas and table
            canvas.classList.remove("d-none");
            tableContainer.classList.remove("d-none");
        }
    }

    /**
     * Generates and updates the table view with the current data subset.
     * @param {Array<Object>} data - The array of exoplanet data.
     */
    function updateDataTable(data) {
        if (data.length === 0) {
            tableContainer.innerHTML = '<p class="text-center mt-5">No detailed data to display.</p>';
            return;
        }

        let tableHtml = `
            <h3 class="text-center mt-5 mb-3 text-white">Detailed Data (${data.length} results)</h3>
            <div class="table-responsive">
            <table class="table table-dark table-hover table-bordered caption-top align-middle custom-exoplanet-table">
                <caption>Top planets displayed in the chart.</caption>
                <thead>
                    <tr>
                        <th>Planet Name</th>
                        <th class="text-end">Distance (ly)</th>
                        <th class="text-end">Radius (R⨁)</th>
                        <th class="text-end">Mass (M⨁)</th>
                        <th class="text-end">Disc. Year</th>
                    </tr>
                </thead>
                <tbody>
        `;

        data.forEach(p => {
            const formatValue = (value) => parseFloat(value).toFixed(2);
            tableHtml += `
                <tr>
                    <td>${p.pl_name || 'N/A'}</td>
                    <td class="text-end">${formatValue(p.sy_dist)}</td>
                    <td class="text-end">${formatValue(p.pl_rade)}</td>
                    <td class="text-end">${formatValue(p.pl_bmasse)}</td>
                    <td class="text-end">${p.disc_year || 'N/A'}</td>
                </tr>
            `;
        });

        tableHtml += `
                </tbody>
            </table>
            </div>
        `;
        tableContainer.innerHTML = tableHtml;

        // The inline styling block for alternating rows has been removed here.
        // The styling will now be handled by external CSS.
    }

    async function fetchData() {
        setLoading(true); // 1. Show loading indicator immediately

        const criterion = criterionSelect?.value || "pl_bmasse";
        const order = orderSelect?.value || "asc";

        try {
            const response = await fetch(`sorted?format=json&criterion=${criterion}&order=${order}`);
            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status} - Could not fetch data.`);
            }

            const data = await response.json();
            console.log("Fetched data sample:", data.slice(0, 3));

            // --- Update Table with all fetched data ---
            updateDataTable(data);

            if (!Array.isArray(data) || data.length === 0) {
                console.warn("No data to render.");
                return;
            }

            // --- SCATTER PLOT DATA SETUP ---
            // Scatter plot axes are fixed: X=Mass, Y=Radius.
            // The criterion selection only controls the sorting order of the data points.
            const chartData = data.map(p => ({
                // X: Mass (use small non-zero value for log scale protection)
                x: parseFloat(p.pl_bmasse) || 0.001,
                // Y: Radius (use small non-zero value for log scale protection)
                y: parseFloat(p.pl_rade) || 0.001,
                pl_name: p.pl_name // custom property for tooltip
            }));

            // The labels are still needed for tooltips
            const labels = data.map(p => p.pl_name);

            // --- SCALES CONFIGURATION ---
            // Use Logarithmic scales for Mass and Radius due to large outliers.
            const scatterScales = {
                x: {
                    type: 'logarithmic', // Log scale for Mass
                    position: 'bottom',
                    title: {
                        display: true,
                        text: 'Planet Mass (M⨁) - Log Scale',
                        color: '#fff'
                    },
                    ticks: { color: "#ddd" },
                    grid: { color: "rgba(255,255,255,0.1)" },
                },
                y: {
                    type: 'logarithmic', // Log scale for Radius
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Planet Radius (R⨁) - Log Scale',
                        color: '#fff'
                    },
                    ticks: { color: "#ddd" },
                    grid: { color: "rgba(255,255,255,0.1)" },
                }
            };
            // ------------------------------------------------------------------


            // Destroy existing chart if present
            if (chart) {
                chart.destroy();
                chart = null; // Clear the reference
            }

            chart = new Chart(ctx, {
                type: "scatter", // Changed to scatter plot
                data: {
                    labels,
                    datasets: [{
                        label: `Exoplanets Mass vs. Radius (Sorted by ${criterion}, ${order})`,
                        data: chartData,
                        backgroundColor: "rgba(138, 43, 226, 0.8)", // Dots are solid purple
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBorderColor: "rgba(255, 255, 255, 0.8)",
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: scatterScales,
                    plugins: {
                        legend: {
                            labels: { color: "#fff" },
                        },
                        title: {
                            display: true,
                            text: 'Exoplanet Mass-Radius Relationship (Fixed Axes)',
                            color: "#fff",
                            font: { size: 16, weight: "bold" },
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    // Use the custom property attached to the data point
                                    const planetName = context.raw.pl_name;
                                    const mass = context.raw.x.toFixed(2);
                                    const radius = context.raw.y.toFixed(2);
                                    return `${planetName} | Mass: ${mass} M⨁, Radius: ${radius} R⨁`;
                                }
                            }
                        }
                    },
                },
            });

        } catch (err) {
            console.error("Error during fetch or chart rendering:", err);
            // Update the loading indicator to show an error message
            loadingIndicator.innerHTML = '<p class="mt-4 fs-3 text-danger">Error loading data. Please try again or check the console for details.</p>';

        } finally {
            // Guarantee that the loading state is exited.
            setLoading(false);
        }
    }

    // Event listeners
    if (criterionSelect) criterionSelect.addEventListener("change", fetchData);
    if (orderSelect) orderSelect.addEventListener("change", fetchData);

    // Initial data fetch
    await fetchData();
});
