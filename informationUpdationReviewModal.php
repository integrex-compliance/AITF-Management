
<!-- this file is created by Gaurav Oli -->




<style>
    #informationUpdationReviewModal .modal-content {
        border-top: 4px solid #f5821f;
    }

    #informationUpdationReviewModal .modal-header {
        background: #fef4ea;
        border-bottom: none;
    }

    #informationUpdationReviewModal .modal-title {
        font-weight: 600;
        color: #333333;
    }

    #informationUpdationReviewModal .info-review-body {
        padding: 25px 30px;
        background: #ffffff;
    }

    #informationUpdationReviewModal .info-review-header {
        border-radius: 2px;
        font-weight: 600;
        background: #f5821f;
        color: #ffffff;
        margin-bottom: 20px;
    }

    #informationUpdationReviewModal .info-review-title h4,
    #informationUpdationReviewModal .info-review-title h5 {
        color: #f5821f;
        font-weight: 600;
        margin: 0;
    }

    #informationUpdationReviewModal .info-review-title p {
        margin-top: 8px;
        font-size: 13px;
        color: #555555;
    }

    #informationUpdationReviewModal .info-review-section {
        border: 1px solid #f5821f;
        padding: 18px 20px;
        margin-bottom: 18px;
        border-radius: 3px;
    }

    #informationUpdationReviewModal .info-review-field {
        border-bottom: 1px solid #f5b27f;
        margin-bottom: 12px;
        padding-bottom: 12px;
    }

    #informationUpdationReviewModal .info-review-field:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    #informationUpdationReviewModal label {
        font-weight: 600;
        color: #333333;
    }

    #informationUpdationReviewModal .info-review-option .radio {
        margin-top: 6px;
        margin-bottom: 0;
    }

    #informationUpdationReviewModal .info-review-input textarea {
        resize: vertical;
    }

    #informationUpdationReviewModal .info-review-footer {
        border-top: 2px solid #f5821f;
        padding-top: 12px;
        margin-top: 12px;
    }

    #informationUpdationReviewModal .modal-footer {
        border-top: none;
        padding-top: 0;
    }

    #informationUpdationReviewModal .modal-footer .btn-info {
        background: #f5821f;
        border-color: #f5821f;
    }

    #informationUpdationReviewModal .modal-footer .btn-info:hover {
        background: #e57300;
        border-color: #e57300;
    }
</style>
<div class="modal fade" id="informationUpdationReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <span class="modal-title">Information Updation Review</span>
            </div>
            <form id="informationUpdationReviewForm" method="post">
                <div class="modal-body info-review-body">
                    <div class="row">
                        <div class="col-xs-12 text-center btn qms_complete_btn info-review-header">
                            <span><i class="fa">Information Updation Review</i></span>
                        </div>
                    </div>
                    <div class="info-review-section">
                        <div class="row">
                            <div class="col-sm-12 text-center info-review-title">
                                <h4>AITF MANAGEMENT CERTIFICATION LIMITED</h4>
                                <h5>REVIEW OF INFORMATION UPDATION</h5>
                                <p>The Review of Information Updation Form before the next audit for the changes submitted by the organisation since the last certification by AITF.</p>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-12 form-group">
                                <label>Information Updation Form Received on Dt.:</label>
                                <input type="text" class="form-control info-review-date" name="info_form_received_date" placeholder="DD.MM.YYYY">
                            </div>
                        </div>
                    </div>

                    <div class="info-review-section">
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Company name – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="company_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="company_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="company_change_details" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Change of organisation ownership – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="ownership_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="ownership_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="ownership_change_details" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Change of Legal Status (Ltd / Pvt Ltd, etc.) – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="legal_status_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="legal_status_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="legal_status_change_details" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Change/addition of Address/location(s) certified – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="address_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="address_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="address_change_details" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Change in number of employees (Addition/Reduction) – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="employee_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="employee_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="employee_change_details" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Change in office/communication address – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="communication_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="communication_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="communication_change_details" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6 info-review-option">
                                <label>Change of certified activity (Addition/Reduction – Scope of audit) – Any Change:</label>
                                <div class="radio"><label><input type="radio" name="activity_change" value="Yes"> Yes</label></div>
                                <div class="radio"><label><input type="radio" name="activity_change" value="No"> No</label></div>
                            </div>
                            <div class="col-sm-6 info-review-input">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="activity_change_details" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="info-review-section">
                        <div class="row info-review-field">
                            <div class="col-sm-12">
                                <label>Review Comments / Conclusion (if any):</label>
                                <textarea class="form-control" name="review_comments" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row info-review-field">
                            <div class="col-sm-6">
                                <label>Name of Reviewer:</label>
                                <input type="text" class="form-control" name="reviewer_name" placeholder="Name of Reviewer">
                            </div>
                            <div class="col-sm-6">
                                <label>Date of Review:</label>
                                <input type="text" class="form-control info-review-date" name="review_date" placeholder="DD.MM.YYYY">
                            </div>
                        </div>
                        <div class="row info-review-footer">
                            <div class="col-sm-12 text-center">
                                <strong>Form No. F 7.31 Annex 1 | Rev:00 | Date: 01.06.2025 | Page: 1 of 1</strong>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="info_review_service_id" class="info-review-service-id" value="">
                    <input type="hidden" name="info_review_customer_id" class="info-review-customer-id" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
