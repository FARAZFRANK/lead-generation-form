/**
 * Lead Generation Form - Dashboard Visual Analytics & Drop-off Funnel (PRO-017)
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        if (typeof WLGFDashboardData === 'undefined' || typeof Chart === 'undefined') {
            return;
        }

        const dataStore = WLGFDashboardData.forms || {};
        let currentFormId = 'all';
        let currentRange = 7;
        let lineChart = null;
        let deviceChart = null;

        const $formFilter = $('#wlgf-analytics-form-filter');
        const $rangeBtns = $('#wlgf-time-range-group .wlgf-btn');
        const $emptyState = $('#wlgf-analytics-empty-state');
        const $contentArea = $('#wlgf-analytics-content-area');
        const $shortcodePreview = $('#wlgf-empty-shortcode-preview');

        function getDatesList(days) {
            const list = [];
            for (let i = days - 1; i >= 0; i--) {
                const d = new Date();
                d.setDate(d.getDate() - i);
                const iso = d.toISOString().split('T')[0];
                list.push(iso);
            }
            return list;
        }

        function getAggregateData(formId, days) {
            const dates = getDatesList(days);
            const views = new Array(dates.length).fill(0);
            const submissions = new Array(dates.length).fill(0);
            const stepViews = {};
            const devices = { desktop: 0, mobile: 0, tablet: 0 };

            const targetForms = (formId === 'all') ? Object.keys(dataStore) : [formId];

            targetForms.forEach(function(fid) {
                const formAnalytics = dataStore[fid] || {};
                const daily = formAnalytics.daily || {};
                const steps = formAnalytics.step_views || {};
                const dev = formAnalytics.device_breakdown || {};

                dates.forEach(function(date, idx) {
                    if (daily[date]) {
                        views[idx] += (daily[date].views || 0);
                        submissions[idx] += (daily[date].submissions || 0);
                    }
                });

                Object.keys(steps).forEach(function(step) {
                    stepViews[step] = (stepViews[step] || 0) + (steps[step] || 0);
                });

                devices.desktop += (dev.desktop || 0);
                devices.mobile += (dev.mobile || 0);
                devices.tablet += (dev.tablet || 0);
            });

            return {
                labels: dates.map(function(d) {
                    const parts = d.split('-');
                    return parts[1] + '/' + parts[2];
                }),
                views: views,
                submissions: submissions,
                stepViews: stepViews,
                devices: devices
            };
        }

        function renderLineChart(agg) {
            const ctx = document.getElementById('wlgf-analytics-chart');
            if (!ctx) return;

            if (lineChart) {
                lineChart.destroy();
            }

            const chartCtx = ctx.getContext('2d');
            const viewGrad = chartCtx.createLinearGradient(0, 0, 0, 260);
            viewGrad.addColorStop(0, 'rgba(37, 99, 235, 0.22)');
            viewGrad.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

            const subGrad = chartCtx.createLinearGradient(0, 0, 0, 260);
            subGrad.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
            subGrad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            lineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: agg.labels,
                    datasets: [
                        {
                            label: 'Form Impressions',
                            data: agg.views,
                            borderColor: '#2563eb',
                            backgroundColor: viewGrad,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#2563eb',
                            borderWidth: 2
                        },
                        {
                            label: 'Submissions Captured',
                            data: agg.submissions,
                            borderColor: '#10b981',
                            backgroundColor: subGrad,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#10b981',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                font: { family: 'inherit', size: 12, weight: 600 },
                                usePointStyle: true,
                                padding: 14
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 6,
                            boxPadding: 4
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#64748b' }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 11 }, color: '#64748b' },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        }
                    }
                }
            });
        }

        function renderDeviceChart(devices) {
            const ctx = document.getElementById('wlgf-device-chart');
            if (!ctx) return;

            if (deviceChart) {
                deviceChart.destroy();
            }

            const total = devices.desktop + devices.mobile + devices.tablet;
            const dataVals = total > 0 ? [devices.desktop, devices.mobile, devices.tablet] : [1, 0, 0];
            const bgColors = total > 0 ? ['#2563eb', '#10b981', '#f59e0b'] : ['#e2e8f0', '#f1f5f9', '#f8fafc'];

            deviceChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Desktop', 'Mobile', 'Tablet'],
                    datasets: [{
                        data: dataVals,
                        backgroundColor: bgColors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                usePointStyle: true,
                                padding: 12,
                                font: { size: 11, weight: 500 }
                            }
                        },
                        tooltip: {
                            enabled: (total > 0),
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 6
                        }
                    }
                }
            });
        }

        function renderFunnel(agg) {
            const $funnelWrap = $('#wlgf-funnel-stages');
            if (!$funnelWrap.length) return;

            const totalViews = agg.views.reduce(function(a, b) { return a + b; }, 0);
            const totalSubs = agg.submissions.reduce(function(a, b) { return a + b; }, 0);
            const stepKeys = Object.keys(agg.stepViews).sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); });

            $funnelWrap.empty();

            const isLowSample = (totalViews < 5);
            const baseViews = totalViews || 1;

            if (!stepKeys.length || (stepKeys.length === 1 && stepKeys[0] === '1')) {
                // Single step or initial form
                const subPct = totalViews > 0 ? Math.min(100, Math.round((totalSubs / baseViews) * 100)) : 0;
                const dropPct = Math.max(0, 100 - subPct);
                const barWidth = Math.max(14, subPct);

                let statusBadge = '';
                if (isLowSample) {
                    statusBadge = `<span class="wlgf-badge wlgf-badge--muted">Collecting baseline (${totalViews} view${totalViews === 1 ? '' : 's'})</span>`;
                } else if (dropPct >= 40) {
                    statusBadge = `<span class="wlgf-funnel-dropoff-alert"><i class="fa-solid fa-triangle-exclamation"></i> -${dropPct}% drop-off</span>`;
                } else {
                    statusBadge = `<span class="wlgf-badge wlgf-badge--success">${subPct}% conversion</span>`;
                }

                $funnelWrap.append(`
                    <div class="wlgf-funnel-stage">
                        <div class="wlgf-funnel-stage__header">
                            <span><i class="fa-solid fa-eye text-primary"></i> <strong>Stage 1: Form Impressions</strong></span>
                            <span class="wlgf-funnel-stage__counts"><strong>${totalViews} views</strong> (100%)</span>
                        </div>
                        <div class="wlgf-funnel-stage__bar-wrap">
                            <div class="wlgf-funnel-stage__bar-fill" style="width: 100%;">100% Views</div>
                        </div>
                    </div>
                    <div class="wlgf-funnel-connector">
                        <div class="wlgf-funnel-connector__line"></div>
                        <span><i class="fa-solid fa-arrow-down text-muted"></i> ${dropPct > 0 ? dropPct + '% drop-off' : '100% conversion'}</span>
                    </div>
                    <div class="wlgf-funnel-stage">
                        <div class="wlgf-funnel-stage__header">
                            <span><i class="fa-solid fa-check-circle text-success"></i> <strong>Stage 2: Completed Submissions</strong></span>
                            <span class="wlgf-funnel-stage__counts">
                                ${statusBadge}
                                <strong class="wlgf-funnel-stage__pct">${subPct}%</strong> (${totalSubs} leads)
                            </span>
                        </div>
                        <div class="wlgf-funnel-stage__bar-wrap">
                            <div class="wlgf-funnel-stage__bar-fill wlgf-funnel-stage__bar-fill--success" style="width: ${barWidth}%;">${subPct}% (${totalSubs})</div>
                        </div>
                    </div>
                `);
            } else {
                // Multi-step wizard breakdown
                let prevCount = totalViews;

                stepKeys.forEach(function(stepNum) {
                    const count = agg.stepViews[stepNum] || 0;
                    const pctOfStart = totalViews > 0 ? Math.min(100, Math.round((count / baseViews) * 100)) : 0;
                    const stepDrop = prevCount > 0 ? Math.max(0, Math.round(((prevCount - count) / prevCount) * 100)) : 0;
                    const stepBarWidth = Math.max(14, pctOfStart);
                    prevCount = count;

                    let stepBadge = '';
                    if (isLowSample) {
                        stepBadge = `<span class="wlgf-badge wlgf-badge--muted">Step ${stepNum}</span>`;
                    } else if (stepDrop >= 40) {
                        stepBadge = `<span class="wlgf-funnel-dropoff-alert"><i class="fa-solid fa-triangle-exclamation"></i> -${stepDrop}% drop-off</span>`;
                    }

                    $funnelWrap.append(`
                        <div class="wlgf-funnel-stage">
                            <div class="wlgf-funnel-stage__header">
                                <span><i class="fa-solid fa-arrow-right-to-bracket text-primary"></i> <strong>Step ${stepNum}: Step Progression</strong></span>
                                <span class="wlgf-funnel-stage__counts">
                                    ${stepBadge}
                                    <strong class="wlgf-funnel-stage__pct">${pctOfStart}%</strong> (${count} views)
                                </span>
                            </div>
                            <div class="wlgf-funnel-stage__bar-wrap">
                                <div class="wlgf-funnel-stage__bar-fill" style="width: ${stepBarWidth}%;">${pctOfStart}%</div>
                            </div>
                        </div>
                        <div class="wlgf-funnel-connector">
                            <div class="wlgf-funnel-connector__line"></div>
                            <span><i class="fa-solid fa-arrow-down text-muted"></i> Step transition</span>
                        </div>
                    `);
                });

                // Final completion stage
                const finalPct = totalViews > 0 ? Math.min(100, Math.round((totalSubs / baseViews) * 100)) : 0;
                const finalBarWidth = Math.max(14, finalPct);
                $funnelWrap.append(`
                    <div class="wlgf-funnel-stage">
                        <div class="wlgf-funnel-stage__header">
                            <span><i class="fa-solid fa-check-circle text-success"></i> <strong>Completion: Submission Captured</strong></span>
                            <span class="wlgf-funnel-stage__counts">
                                <strong class="wlgf-funnel-stage__pct">${finalPct}%</strong> (${totalSubs} leads)
                            </span>
                        </div>
                        <div class="wlgf-funnel-stage__bar-wrap">
                            <div class="wlgf-funnel-stage__bar-fill wlgf-funnel-stage__bar-fill--success" style="width: ${finalBarWidth}%;">${finalPct}% (${totalSubs})</div>
                        </div>
                    </div>
                `);
            }
        }

        function updateAll() {
            const agg = getAggregateData(currentFormId, currentRange);
            const totalImpressions = agg.views.reduce(function(a, b) { return a + b; }, 0);
            const totalSubs = agg.submissions.reduce(function(a, b) { return a + b; }, 0);

            // Empty state check
            if (totalImpressions === 0 && totalSubs === 0) {
                const formKeys = Object.keys(dataStore);
                if (formKeys.length === 0) {
                    $('#wlgf-analytics-empty-title').text('No Forms Created Yet');
                    $('#wlgf-analytics-empty-desc').text('You have not created any forms yet. Create your first lead generation form using our drag & drop builder or pre-built templates to start capturing leads and tracking conversions.');
                    $('#wlgf-analytics-empty-embed').hide();
                    $('#wlgf-empty-manage-btn').hide();
                    $('#wlgf-empty-create-btn').removeClass('wlgf-btn--outline').addClass('wlgf-btn--primary');
                    $('#wlgf-empty-create-btn-text').text('Create Your First Form');
                } else {
                    const activeId = (currentFormId !== 'all' && currentFormId) ? currentFormId : formKeys[0];
                    $('#wlgf-analytics-empty-title').text('Start Tracking Form Conversions');
                    $('#wlgf-analytics-empty-desc').text('Embed your form shortcode on any page or post to automatically track visitor views, submission velocity, and step drop-offs.');
                    $shortcodePreview.text('[WLFG id="' + activeId + '"]');
                    $('#wlgf-analytics-empty-embed').show();
                    $('#wlgf-empty-manage-btn').show();
                    $('#wlgf-empty-create-btn').removeClass('wlgf-btn--primary').addClass('wlgf-btn--outline');
                    $('#wlgf-empty-create-btn-text').text('Create Another Form');
                }
                $emptyState.show();
                $contentArea.hide();
            } else {
                $emptyState.hide();
                $contentArea.show();
                renderLineChart(agg);
                renderDeviceChart(agg.devices);
                renderFunnel(agg);
            }
        }

        // Form filter change handler
        $formFilter.on('change', function() {
            currentFormId = $(this).val();
            updateAll();
        });

        // Time range buttons handler
        $rangeBtns.on('click', function(e) {
            e.preventDefault();
            $rangeBtns.removeClass('wlgf-btn--primary').addClass('wlgf-btn--outline');
            $(this).removeClass('wlgf-btn--outline').addClass('wlgf-btn--primary');
            currentRange = parseInt($(this).data('range'), 10) || 7;
            updateAll();
        });

        // Quick-Start Guide Collapse / Expand Toggle
        const $toggleBtn = $('#wlgf-toggle-quickstart-btn');
        const $guideBody = $('#wlgf-quickstart-body');
        if ($toggleBtn.length && $guideBody.length) {
            const isCollapsed = localStorage.getItem('wlgf_quickstart_collapsed') === 'true';
            if (isCollapsed) {
                $guideBody.hide();
                $toggleBtn.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }

            $toggleBtn.on('click', function(e) {
                e.preventDefault();
                $guideBody.slideToggle(200, function() {
                    const hidden = $guideBody.is(':hidden');
                    localStorage.setItem('wlgf_quickstart_collapsed', hidden ? 'true' : 'false');
                    $toggleBtn.find('i').toggleClass('fa-chevron-up', !hidden).toggleClass('fa-chevron-down', hidden);
                });
            });
        }

        // Initial render
        updateAll();
    });
})(jQuery);
