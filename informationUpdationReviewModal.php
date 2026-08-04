
<!-- this file is created by Gaurav Oli -->

<script src="js/form-fancy-control.js"></script>
<link rel="stylesheet" href="css/float-label.css">

<style>
    #informationUpdationReviewModal .radio-inline input[type="radio"] {
        margin-top: 2px;
    }
</style>

<div class="modal fade" id="informationUpdationReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
                Information Updation Review
            </div>
            <form id="informationUpdationReviewForm" method="post">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 text-center btn qms_complete_btn">
                            <span><i class="fa">Information Updation Review</i></span>
                        </div>
                    </div>

                    <div style="padding-top: 15px; padding-bottom: 15px; margin-bottom: 20px; border-bottom: 1px solid #eee;">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <p style="font-size: 13px; color: #666; margin-top: 8px; line-height: 1.4;">The Review of Information Updation Form before the next audit for the changes submitted by the organisation since the last certification by AITF.</p>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <!-- Row 1 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Company name – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="company_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="company_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="company_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Change of organisation ownership – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="ownership_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="ownership_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="ownership_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Row 3 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Change of Legal Status (Ltd / Pvt Ltd, etc.) – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="legal_status_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="legal_status_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="legal_status_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Row 4 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Change/addition of Address/location(s) certified – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="address_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="address_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="address_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Row 5 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Change in number of employees (Addition/Reduction) – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="employee_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="employee_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="employee_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Row 6 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Change in office/communication address – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="communication_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="communication_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="communication_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Row 7 -->
                        <div class="row" style="margin-bottom: 15px; padding-bottom: 15px;">
                            <div class="col-sm-6 form-group">
                                <label style="font-weight: 600; color: #333;">Change of certified activity (Addition/Reduction – Scope of audit) – Any Change:</label>
                                <div style="margin-top: 5px;">
                                    <label class="radio-inline" style="font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="activity_change" value="Yes" style="cursor: pointer;"> Yes
                                    </label>
                                    <label class="radio-inline" style="margin-left: 20px; font-weight: normal; color: #555; cursor: pointer;">
                                        <input type="radio" name="activity_change" value="No" style="cursor: pointer;"> No
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>If change, Elaborate:</label>
                                <textarea class="form-control" name="activity_change_details" placeholder="If change, Elaborate:" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 10px;">
                        <div class="row">
                            <div class="col-sm-12 form-group float-label-control">
                                <label>Review Comments / Conclusion (if any):</label>
                                <textarea class="form-control" name="review_comments" placeholder="Review Comments / Conclusion (if any):" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Name of Reviewer:</label>
                                <input type="text" class="form-control" name="reviewer_name" placeholder="Name of Reviewer">
                            </div>
                            <div class="col-sm-6 form-group float-label-control">
                                <label>Date of Review:</label>
                                <input type="text" class="form-control info-review-date" name="review_date" placeholder="Date of Review:">
                                <span id="info_review_date_error" style="color: red; font-size: 12px; display: none; margin-top: 5px;"></span>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee;">
                            <div class="col-sm-12 text-center" style="font-size: 12px; color: #777;">
                                <strong>Form No. F 7.31 Annex 1 | Rev:00 | Date: 01.06.2025 | Page: 1 of 1</strong>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="info_review_service_id" class="info-review-service-id" value="">
                    <input type="hidden" name="info_review_customer_id" class="info-review-customer-id" value="">
                    <input type="hidden" name="info_review_suspend_date" id="info_review_suspend_date" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
