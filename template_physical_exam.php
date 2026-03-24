<!-- ===============================
     Physical & Pelvic Exam Summary Template
     Import using: include 'physical_exam_summary.php';
     =============================== -->

<div class="exam-summary-container">

    <!-- Physical Exam Summary Card -->
    <div class="exam-card">
        <div class="exam-card-header"></div>
        <div class="exam-card-title">
            <div class="exam-icon">🔍</div>
            <h3>Physical Examination</h3>
        </div>

        <div class="exam-section">
            <div class="exam-item"><span>Conjunctiva</span><span id="summary-conjunctiva">—</span></div>
            <div class="exam-item"><span>Neck</span><span id="summary-neck">—</span></div>
            <div class="exam-item"><span>Thorax</span><span id="summary-thorax">—</span></div>
            <div class="exam-item"><span>Abdomen</span><span id="summary-abdomen">—</span></div>
            <div class="exam-item"><span>Extremities</span><span id="summary-extremities">—</span></div>

            <!-- Left Breast Section -->
            <div class="exam-subcard">
                <div class="exam-subtitle">🫀 LEFT BREAST</div>
                <div class="exam-subsection">
                    <div class="exam-subitem"><span>Mass</span><span id="summary-breast-left-mass">—</span></div>
                    <div class="exam-subitem"><span>Nipple Discharge</span><span id="summary-breast-left-nipple">—</span></div>
                    <div class="exam-subitem"><span>Skin Changes</span><span id="summary-breast-left-skin">—</span></div>
                    <div class="exam-subitem"><span>Axillary Lymph Nodes</span><span id="summary-breast-left-axillary">—</span></div>
                </div>
            </div>

            <!-- Right Breast Section -->
            <div class="exam-subcard">
                <div class="exam-subtitle">🫀 RIGHT BREAST</div>
                <div class="exam-subsection">
                    <div class="exam-subitem"><span>Mass</span><span id="summary-breast-right-mass">—</span></div>
                    <div class="exam-subitem"><span>Nipple Discharge</span><span id="summary-breast-right-nipple">—</span></div>
                    <div class="exam-subitem"><span>Skin Changes</span><span id="summary-breast-right-skin">—</span></div>
                    <div class="exam-subitem"><span>Axillary Lymph Nodes</span><span id="summary-breast-right-axillary">—</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pelvic Examination Card -->
    <div class="exam-card">
        <div class="exam-card-header"></div>
        <div class="exam-card-title">
            <div class="exam-icon">🩺</div>
            <h3>Pelvic Examination</h3>
        </div>

        <div class="exam-section">
            <div class="exam-item"><span>Perinium</span><span id="summary-perinium">—</span></div>
            <div class="exam-item"><span>Vagina</span><span id="summary-vagina">—</span></div>
            <div class="exam-item"><span>ADNEXA</span><span id="summary-adnexa">—</span></div>
            <div class="exam-item"><span>Cervix</span><span id="summary-cervix">—</span></div>
            <div class="exam-item"><span>Uterus</span><span id="summary-uterus">—</span></div>
            <div class="exam-item"><span>Uterine Depth</span><span id="summary-uterine-depth">—</span></div>
        </div>
    </div>
</div>

<!-- ===============================
     Inline CSS (can move to a CSS file)
     =============================== -->
<style>
.exam-summary-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
    width: 100%;
}

/* Main Card */
.exam-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 24px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    width: 100%;
}
.exam-card-header {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}
.exam-card-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.exam-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}
.exam-card-title h3 {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

/* Exam Item Styles */
.exam-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.exam-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f1f5f9;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}
.exam-item span:first-child {
    color: #475569;
    font-weight: 600;
    font-size: 14px;
}
.exam-item span:last-child {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
}

/* Subcards (Breast Sections) */
.exam-subcard {
    background: #f8fafc;
    border-radius: 8px;
    padding: 16px;
    border: 1px solid #e2e8f0;
}
.exam-subtitle {
    color: #475569;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
}
.exam-subsection {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.exam-subitem {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 8px 12px;
    background: #f1f5f9;
    border-radius: 6px;
    border-left: 3px solid #667eea;
}
.exam-subitem span:first-child {
    color: #475569;
    font-weight: 500;
    font-size: 13px;
}
.exam-subitem span:last-child {
    color: #64748b;
    font-size: 12px;
    font-weight: 500;
    max-width: 200px;
    text-align: right;
}
</style>
