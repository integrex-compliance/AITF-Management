<?php
$file = 'loginSelection.php';
$content = file_get_contents($file);

$brokenHTML = '                    <div class="captcha-note" style="margin-top: 24px;">OTP is valid for 10 minutes. After validation, you will be logged in automatically.</div>
    <!-- Forgot Password Modal -->';

$fixedHTML = '                    <div class="captcha-note" style="margin-top: 24px;">OTP is valid for 10 minutes. After validation, you will be logged in automatically.</div>
                <?php } else { ?>
                    <form action="createAccount.php" method="post" class="auth-form">
                        <input type="hidden" name="create_account_action" value="request_otp">
                        <div class="auth-field">
                            <label>Registered Email</label>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope icon-left"></i>
                                <input class="auth-input" type="email" name="signup_email" placeholder="email@company.com" required>
                            </div>
                        </div>
                        
                        <p style="font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 8px; margin-top: 4px; font-weight: 500;">Password must be at least 8 chars, 1 uppercase, 1 special char.</p>
                        <div class="form-row">
                            <div class="auth-field">
                                <label>Create Password</label>
                                <div class="input-wrapper">
                                    <i class="fa fa-lock icon-left"></i>
                                    <input class="auth-input" type="password" name="signup_password" id="signupPassword" placeholder="••••••••" pattern="(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}" title="Must contain at least 8 characters, one capital letter, and one special character" required>
                                    <button type="button" class="password-toggle" data-toggle-password="#signupPassword" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="auth-field">
                                <label>Confirm Password</label>
                                <div class="input-wrapper">
                                    <i class="fa fa-lock icon-left"></i>
                                    <input class="auth-input" type="password" name="signup_confirm_password" id="signupConfirmPassword" placeholder="••••••••" required>
                                    <button type="button" class="password-toggle" data-toggle-password="#signupConfirmPassword" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <ul class="pw-req-list" id="pw-requirements">
                            <li class="pw-req-item unmet" id="req-length"><i class="fa fa-times-circle"></i> At least 8 characters</li>
                            <li class="pw-req-item unmet" id="req-upper"><i class="fa fa-times-circle"></i> At least one capital letter</li>
                            <li class="pw-req-item unmet" id="req-special"><i class="fa fa-times-circle"></i> At least one special character</li>
                        </ul>
                        
                        <button type="submit" class="auth-submit">Continue</button>
                    </form>
                    <div class="captcha-note" style="margin-top: 24px;">Use the email registered by your Director. An OTP will be sent to this address.</div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->';

$content = str_replace($brokenHTML, $fixedHTML, $content);
file_put_contents($file, $content);
echo "HTML really fixed this time.\n";
?>
