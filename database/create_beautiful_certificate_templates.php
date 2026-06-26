<?php
/**
 * Create 10 Beautiful Certificate Templates
 * 
 * This script creates 10 professionally designed certificate templates
 * for different certificate types: graduation, completion, promotion, etc.
 * 
 * Run this script once to populate the certificate templates table
 */

// Get the base directory
$baseDir = dirname(__DIR__);
require_once $baseDir . '/config/config.php';
require_once $baseDir . '/config/database.php';

// Check if user is logged in (optional - can be run from command line)
if (php_sapi_name() !== 'cli') {
    requireLogin();
    requireRole(['Super Admin', 'Admin']);
}

echo "<h2>Creating Beautiful Certificate Templates...</h2>";
echo "<pre>";

$templates = [
    // Template 1: Elegant Graduation Certificate (Landscape)
    [
        'template_name' => 'Elegant Graduation Certificate',
        'certificate_type' => 'graduation',
        'page_orientation' => 'landscape',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 40px; padding-top: 20px;">
    <div style="border-top: 4px solid #1a5490; border-bottom: 4px solid #1a5490; padding: 15px 0; margin-bottom: 15px;">
        <h1 style="font-family: \'Playfair Display\', \'Times New Roman\', serif; font-size: 64px; font-weight: 700; color: #1a5490; margin: 0; letter-spacing: 3px; text-transform: uppercase;">CERTIFICATE</h1>
    </div>
    <h2 style="font-family: \'Georgia\', serif; font-size: 28px; font-weight: 400; color: #2c3e50; margin: 10px 0 0 0; font-style: italic; letter-spacing: 2px;">OF GRADUATION</h2>
    <div style="margin-top: 20px;">
        <p style="font-size: 18px; color: #7f8c8d; margin: 0;">{{SCHOOL_NAME}}</p>
    </div>
</div>',
        'body_html' => '<div style="text-align: center; padding: 40px 20px;">
    <p style="font-size: 20px; color: #34495e; margin-bottom: 30px; font-family: \'Georgia\', serif; line-height: 1.8;">
        This is to certify that
    </p>
    <div style="margin: 30px 0; padding: 20px 0; border-top: 3px solid #d4af37; border-bottom: 3px solid #d4af37;">
        <h3 style="font-family: \'Playfair Display\', serif; font-size: 42px; font-weight: 700; color: #1a5490; margin: 0; letter-spacing: 2px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 18px; color: #34495e; margin: 30px 0; font-family: \'Georgia\', serif; line-height: 1.8;">
        has successfully completed the requirements for <strong style="color: #1a5490;">{{CLASS}}</strong><br>
        during the academic session <strong style="color: #1a5490;">{{SESSION}}</strong><br>
        and is hereby awarded this Certificate of Graduation.
    </p>
    <div style="margin-top: 40px; padding: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #1a5490;">
        <p style="font-size: 14px; color: #495057; margin: 5px 0;">
            <strong>Certificate Number:</strong> {{CERTIFICATE_ID}}
        </p>
        <p style="font-size: 14px; color: #495057; margin: 5px 0;">
            <strong>Date of Issue:</strong> {{DATE}}
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 60px; padding: 0 40px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #1a5490; width: 200px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 600; font-size: 16px; color: #1a5490; margin: 5px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #1a5490; width: 200px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 600; font-size: 16px; color: #1a5490; margin: 5px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Registrar</p>
    </div>
</div>
{{QR_CODE}}',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 1,
        'is_default' => 0
    ],

    // Template 2: Classic Completion Certificate (Portrait)
    [
        'template_name' => 'Classic Completion Certificate',
        'certificate_type' => 'completion',
        'page_orientation' => 'portrait',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 50px; position: relative;">
    <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 100px; border: 5px solid #d4af37; border-radius: 50%; background: rgba(212, 175, 55, 0.1);"></div>
    <h1 style="font-family: \'Old Standard TT\', \'Times New Roman\', serif; font-size: 56px; font-weight: 700; color: #2c3e50; margin: 60px 0 15px 0; letter-spacing: 4px; text-transform: uppercase;">CERTIFICATE</h1>
    <h2 style="font-family: \'Crimson Text\', serif; font-size: 32px; font-weight: 400; color: #7f8c8d; margin: 0; font-style: italic;">OF COMPLETION</h2>
    <div style="margin-top: 25px;">
        <p style="font-size: 16px; color: #95a5a6; letter-spacing: 1px;">{{SCHOOL_NAME}}</p>
    </div>
</div>',
        'body_html' => '<div style="text-align: center; padding: 30px 40px; font-family: \'Crimson Text\', \'Georgia\', serif;">
    <p style="font-size: 22px; color: #34495e; margin-bottom: 25px; line-height: 2;">
        This is to certify that
    </p>
    <div style="margin: 25px 0; padding: 25px 0;">
        <h3 style="font-family: \'Playfair Display\', serif; font-size: 48px; font-weight: 700; color: #1a5490; margin: 0; letter-spacing: 1px; text-decoration: underline; text-decoration-color: #d4af37; text-decoration-thickness: 3px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 19px; color: #2c3e50; margin: 30px 0; line-height: 2;">
        has successfully completed the course requirements for<br>
        <strong style="color: #1a5490; font-size: 22px;">{{CLASS}}</strong><br>
        in the academic session <strong style="color: #1a5490;">{{SESSION}}</strong>
    </p>
    <div style="margin-top: 35px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 5px solid #d4af37;">
        <p style="font-size: 15px; color: #495057; margin: 8px 0;">
            <strong>Certificate ID:</strong> {{CERTIFICATE_ID}}
        </p>
        <p style="font-size: 15px; color: #495057; margin: 8px 0;">
            <strong>Issued on:</strong> {{DATE}}
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 80px; padding: 0 50px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #2c3e50; width: 180px; margin: 0 auto 12px; padding-top: 10px;"></div>
        <p style="font-weight: 700; font-size: 18px; color: #2c3e50; margin: 8px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #2c3e50; width: 180px; margin: 0 auto 12px; padding-top: 10px;"></div>
        <p style="font-weight: 700; font-size: 18px; color: #2c3e50; margin: 8px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 30px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 0,
        'is_default' => 1
    ],

    // Template 3: Modern Achievement Certificate (Landscape)
    [
        'template_name' => 'Modern Achievement Certificate',
        'certificate_type' => 'achievement',
        'page_orientation' => 'landscape',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
    <h1 style="font-family: \'Montserrat\', \'Arial\', sans-serif; font-size: 58px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: 2px; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">ACHIEVEMENT</h1>
    <h2 style="font-family: \'Montserrat\', sans-serif; font-size: 24px; font-weight: 300; color: #f0f0f0; margin: 10px 0 0 0; letter-spacing: 3px;">CERTIFICATE</h2>
    <p style="font-size: 16px; color: #e0e0e0; margin-top: 15px; letter-spacing: 1px;">{{SCHOOL_NAME}}</p>
</div>',
        'body_html' => '<div style="text-align: center; padding: 35px 25px;">
    <p style="font-size: 20px; color: #2c3e50; margin-bottom: 25px; font-family: \'Open Sans\', sans-serif; font-weight: 300;">
        This certificate is proudly presented to
    </p>
    <div style="margin: 30px 0; padding: 25px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">
        <h3 style="font-family: \'Montserrat\', sans-serif; font-size: 44px; font-weight: 700; color: #667eea; margin: 0; letter-spacing: 1px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 18px; color: #34495e; margin: 30px 0; font-family: \'Open Sans\', sans-serif; line-height: 1.8;">
        in recognition of outstanding achievement and excellence in<br>
        <strong style="color: #667eea; font-size: 20px;">{{CLASS}}</strong><br>
        during the <strong style="color: #667eea;">{{SESSION}}</strong> academic year
    </p>
    <div style="margin-top: 35px; display: inline-block; padding: 15px 30px; background: #667eea; border-radius: 25px; color: white;">
        <p style="font-size: 14px; margin: 5px 0; font-weight: 600;">Certificate No: {{CERTIFICATE_ID}}</p>
        <p style="font-size: 14px; margin: 5px 0; font-weight: 600;">Date: {{DATE}}</p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 50px; padding: 0 30px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #667eea; width: 180px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 600; font-size: 16px; color: #667eea; margin: 5px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #667eea; width: 180px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 600; font-size: 16px; color: #667eea; margin: 5px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 25px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 0,
        'is_default' => 0
    ],

    // Template 4: Traditional Promotion Certificate (Portrait)
    [
        'template_name' => 'Traditional Promotion Certificate',
        'certificate_type' => 'promotion',
        'page_orientation' => 'portrait',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 45px; position: relative;">
    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; border: 4px solid #c0392b; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(192, 57, 43, 0.1);">
            <span style="font-size: 30px; color: #c0392b;">✓</span>
        </div>
    </div>
    <h1 style="font-family: \'Merriweather\', \'Times New Roman\', serif; font-size: 52px; font-weight: 900; color: #c0392b; margin: 15px 0; letter-spacing: 3px; text-transform: uppercase;">PROMOTION</h1>
    <h2 style="font-family: \'Merriweather\', serif; font-size: 28px; font-weight: 400; color: #7f8c8d; margin: 0; font-style: italic;">CERTIFICATE</h2>
    <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #ecf0f1;">
        <p style="font-size: 15px; color: #95a5a6; letter-spacing: 1.5px;">{{SCHOOL_NAME}}</p>
    </div>
</div>',
        'body_html' => '<div style="text-align: center; padding: 35px 45px; font-family: \'Merriweather\', \'Georgia\', serif;">
    <p style="font-size: 21px; color: #2c3e50; margin-bottom: 30px; line-height: 2;">
        This is to certify that
    </p>
    <div style="margin: 30px 0; padding: 30px 0; border-top: 4px double #c0392b; border-bottom: 4px double #c0392b;">
        <h3 style="font-family: \'Merriweather\', serif; font-size: 46px; font-weight: 700; color: #c0392b; margin: 0; letter-spacing: 1px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 19px; color: #34495e; margin: 35px 0; line-height: 2;">
        has been promoted from <strong style="color: #c0392b;">{{CLASS}}</strong><br>
        for the academic session <strong style="color: #c0392b;">{{SESSION}}</strong><br>
        in recognition of satisfactory academic performance and conduct.
    </p>
    <div style="margin-top: 40px; padding: 18px; background: #fff5f5; border: 2px solid #c0392b; border-radius: 8px;">
        <p style="font-size: 14px; color: #2c3e50; margin: 6px 0;">
            <strong>Promotion Certificate No:</strong> {{CERTIFICATE_ID}}
        </p>
        <p style="font-size: 14px; color: #2c3e50; margin: 6px 0;">
            <strong>Date of Promotion:</strong> {{DATE}}
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 70px; padding: 0 55px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #c0392b; width: 170px; margin: 0 auto 12px; padding-top: 10px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #c0392b; margin: 8px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #c0392b; width: 170px; margin: 0 auto 12px; padding-top: 10px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #c0392b; margin: 8px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 25px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 1,
        'is_default' => 0
    ],

    // Template 5: Elegant Character Certificate (Portrait)
    [
        'template_name' => 'Elegant Character Certificate',
        'certificate_type' => 'character',
        'page_orientation' => 'portrait',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 50px;">
    <div style="border: 6px double #27ae60; padding: 25px; margin-bottom: 20px; display: inline-block;">
        <h1 style="font-family: \'Lora\', \'Times New Roman\', serif; font-size: 50px; font-weight: 700; color: #27ae60; margin: 0; letter-spacing: 4px; text-transform: uppercase;">CHARACTER</h1>
    </div>
    <h2 style="font-family: \'Lora\', serif; font-size: 30px; font-weight: 400; color: #2c3e50; margin: 15px 0 0 0; font-style: italic;">CERTIFICATE</h2>
    <div style="margin-top: 25px;">
        <p style="font-size: 16px; color: #7f8c8d; letter-spacing: 2px;">{{SCHOOL_NAME}}</p>
    </div>
</div>',
        'body_html' => '<div style="text-align: center; padding: 30px 40px; font-family: \'Lora\', \'Georgia\', serif;">
    <p style="font-size: 20px; color: #2c3e50; margin-bottom: 25px; line-height: 2;">
        This is to certify that
    </p>
    <div style="margin: 25px 0; padding: 20px 0;">
        <h3 style="font-family: \'Lora\', serif; font-size: 44px; font-weight: 700; color: #27ae60; margin: 0; letter-spacing: 1px;">{{STUDENT_NAME}}</h3>
        <p style="font-size: 16px; color: #7f8c8d; margin: 10px 0 0 0;">Student ID: {{STUDENT_ID}}</p>
    </div>
    <p style="font-size: 18px; color: #34495e; margin: 30px 0; line-height: 2;">
        was a student of this institution in <strong style="color: #27ae60;">{{CLASS}}</strong><br>
        during the academic session <strong style="color: #27ae60;">{{SESSION}}</strong><br>
        and has shown exemplary character, conduct, and behavior throughout the period.
    </p>
    <div style="margin-top: 35px; padding: 20px; background: #e8f8f5; border-left: 5px solid #27ae60; border-radius: 5px;">
        <p style="font-size: 15px; color: #2c3e50; margin: 8px 0;">
            <strong>Certificate Number:</strong> {{CERTIFICATE_ID}}
        </p>
        <p style="font-size: 15px; color: #2c3e50; margin: 8px 0;">
            <strong>Date of Issue:</strong> {{DATE}}
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 75px; padding: 0 50px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #27ae60; width: 175px; margin: 0 auto 12px; padding-top: 10px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #27ae60; margin: 8px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #27ae60; width: 175px; margin: 0 auto 12px; padding-top: 10px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #27ae60; margin: 8px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 30px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 0,
        'is_default' => 0
    ],

    // Template 6: Vibrant Participation Certificate (Landscape)
    [
        'template_name' => 'Vibrant Participation Certificate',
        'certificate_type' => 'participation',
        'page_orientation' => 'landscape',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 30px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 25px 15px; border-radius: 12px; box-shadow: 0 5px 20px rgba(245, 87, 108, 0.3);">
    <h1 style="font-family: \'Poppins\', \'Arial\', sans-serif; font-size: 54px; font-weight: 800; color: #ffffff; margin: 0; letter-spacing: 2px; text-transform: uppercase; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">PARTICIPATION</h1>
    <h2 style="font-family: \'Poppins\', sans-serif; font-size: 22px; font-weight: 300; color: #ffffff; margin: 8px 0 0 0; letter-spacing: 2px;">CERTIFICATE</h2>
    <p style="font-size: 15px; color: #ffffff; margin-top: 12px; opacity: 0.95; letter-spacing: 1px;">{{SCHOOL_NAME}}</p>
</div>',
        'body_html' => '<div style="text-align: center; padding: 30px 20px;">
    <p style="font-size: 19px; color: #2c3e50; margin-bottom: 22px; font-family: \'Poppins\', sans-serif; font-weight: 400;">
        This certificate is awarded to
    </p>
    <div style="margin: 25px 0; padding: 22px; background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); border-radius: 12px; box-shadow: 0 3px 12px rgba(0,0,0,0.1);">
        <h3 style="font-family: \'Poppins\', sans-serif; font-size: 40px; font-weight: 700; color: #2d3436; margin: 0; letter-spacing: 1px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 17px; color: #34495e; margin: 28px 0; font-family: \'Poppins\', sans-serif; line-height: 1.8;">
        for active participation and valuable contribution in<br>
        <strong style="color: #f5576c; font-size: 19px;">{{CLASS}}</strong><br>
        during the <strong style="color: #f5576c;">{{SESSION}}</strong> academic session
    </p>
    <div style="margin-top: 30px; display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 20px; color: white; box-shadow: 0 3px 10px rgba(245, 87, 108, 0.3);">
        <p style="font-size: 13px; margin: 4px 0; font-weight: 600;">Cert. No: {{CERTIFICATE_ID}} | Date: {{DATE}}</p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 45px; padding: 0 25px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #f5576c; width: 170px; margin: 0 auto 9px; padding-top: 7px;"></div>
        <p style="font-weight: 600; font-size: 15px; color: #f5576c; margin: 5px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #f5576c; width: 170px; margin: 0 auto 9px; padding-top: 7px;"></div>
        <p style="font-weight: 600; font-size: 15px; color: #f5576c; margin: 5px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 20px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 0,
        'is_default' => 0
    ],

    // Template 7: Premium Graduation Certificate (Landscape)
    [
        'template_name' => 'Premium Graduation Certificate',
        'certificate_type' => 'graduation',
        'page_orientation' => 'landscape',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 40px; position: relative;">
    <div style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); width: 120px; height: 120px; border: 6px solid #8b4513; border-radius: 50%; background: linear-gradient(135deg, #daa520 0%, #b8860b 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);">
        <span style="font-size: 50px; color: #ffffff; font-weight: bold;">🎓</span>
    </div>
    <h1 style="font-family: \'Cinzel\', \'Times New Roman\', serif; font-size: 62px; font-weight: 700; color: #8b4513; margin: 70px 0 12px 0; letter-spacing: 4px; text-transform: uppercase;">GRADUATION</h1>
    <h2 style="font-family: \'Cinzel\', serif; font-size: 26px; font-weight: 400; color: #a0522d; margin: 0; font-style: italic; letter-spacing: 2px;">CERTIFICATE OF EXCELLENCE</h2>
    <div style="margin-top: 20px; padding-top: 15px; border-top: 3px solid #daa520;">
        <p style="font-size: 17px; color: #8b7355; letter-spacing: 1.5px; font-weight: 500;">{{SCHOOL_NAME}}</p>
    </div>
</div>',
        'body_html' => '<div style="text-align: center; padding: 35px 30px; font-family: \'Cinzel\', \'Georgia\', serif;">
    <p style="font-size: 21px; color: #2c3e50; margin-bottom: 28px; line-height: 1.9;">
        This is to certify that
    </p>
    <div style="margin: 28px 0; padding: 28px 0; border-top: 5px solid #daa520; border-bottom: 5px solid #daa520; background: linear-gradient(to bottom, rgba(218, 165, 32, 0.05), transparent);">
        <h3 style="font-family: \'Cinzel\', serif; font-size: 46px; font-weight: 700; color: #8b4513; margin: 0; letter-spacing: 2px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 19px; color: #34495e; margin: 32px 0; line-height: 1.9;">
        has successfully completed all requirements and graduated from<br>
        <strong style="color: #8b4513; font-size: 22px;">{{CLASS}}</strong><br>
        with distinction during the <strong style="color: #8b4513;">{{SESSION}}</strong> academic year.
    </p>
    <div style="margin-top: 38px; padding: 18px; background: #faf8f3; border: 3px solid #daa520; border-radius: 8px; display: inline-block;">
        <p style="font-size: 14px; color: #2c3e50; margin: 6px 0; font-weight: 600;">
            <strong>Graduation Certificate No:</strong> {{CERTIFICATE_ID}}
        </p>
        <p style="font-size: 14px; color: #2c3e50; margin: 6px 0; font-weight: 600;">
            <strong>Date of Graduation:</strong> {{DATE}}
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 55px; padding: 0 35px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #8b4513; width: 190px; margin: 0 auto 11px; padding-top: 9px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #8b4513; margin: 7px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #8b7355; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 500;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #8b4513; width: 190px; margin: 0 auto 11px; padding-top: 9px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #8b4513; margin: 7px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #8b7355; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 500;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 25px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 1,
        'is_default' => 0
    ],

    // Template 8: Minimalist Completion Certificate (Portrait)
    [
        'template_name' => 'Minimalist Completion Certificate',
        'certificate_type' => 'completion',
        'page_orientation' => 'portrait',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 55px; padding-top: 30px;">
    <div style="width: 80px; height: 4px; background: #3498db; margin: 0 auto 25px;"></div>
    <h1 style="font-family: \'Raleway\', \'Arial\', sans-serif; font-size: 48px; font-weight: 300; color: #2c3e50; margin: 0; letter-spacing: 8px; text-transform: uppercase;">CERTIFICATE</h1>
    <h2 style="font-family: \'Raleway\', sans-serif; font-size: 24px; font-weight: 300; color: #7f8c8d; margin: 12px 0 0 0; letter-spacing: 4px;">OF COMPLETION</h2>
    <div style="width: 80px; height: 4px; background: #3498db; margin: 25px auto 0;"></div>
    <p style="font-size: 14px; color: #95a5a6; margin-top: 20px; letter-spacing: 2px; font-weight: 300;">{{SCHOOL_NAME}}</p>
</div>',
        'body_html' => '<div style="text-align: center; padding: 40px 50px; font-family: \'Raleway\', sans-serif;">
    <p style="font-size: 20px; color: #34495e; margin-bottom: 30px; font-weight: 300; line-height: 2;">
        This is to certify that
    </p>
    <div style="margin: 30px 0; padding: 25px 0;">
        <h3 style="font-family: \'Raleway\', sans-serif; font-size: 42px; font-weight: 400; color: #3498db; margin: 0; letter-spacing: 2px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 18px; color: #2c3e50; margin: 35px 0; font-weight: 300; line-height: 2;">
        has successfully completed<br>
        <strong style="color: #3498db; font-weight: 500;">{{CLASS}}</strong><br>
        for the academic session <strong style="color: #3498db; font-weight: 500;">{{SESSION}}</strong>
    </p>
    <div style="margin-top: 40px; padding: 15px; border: 1px solid #ecf0f1; background: #f8f9fa;">
        <p style="font-size: 13px; color: #7f8c8d; margin: 5px 0; font-weight: 300;">
            Certificate ID: <strong style="color: #2c3e50;">{{CERTIFICATE_ID}}</strong>
        </p>
        <p style="font-size: 13px; color: #7f8c8d; margin: 5px 0; font-weight: 300;">
            Issued: <strong style="color: #2c3e50;">{{DATE}}</strong>
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 85px; padding: 0 60px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 1px solid #3498db; width: 160px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 500; font-size: 16px; color: #3498db; margin: 6px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 11px; color: #95a5a6; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 300;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 1px solid #3498db; width: 160px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 500; font-size: 16px; color: #3498db; margin: 6px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 11px; color: #95a5a6; text-transform: uppercase; letter-spacing: 1px; margin: 0; font-weight: 300;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 30px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 0,
        'is_default' => 0
    ],

    // Template 9: Royal Achievement Certificate (Landscape)
    [
        'template_name' => 'Royal Achievement Certificate',
        'certificate_type' => 'achievement',
        'page_orientation' => 'landscape',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 35px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 28px 18px; border-radius: 15px; box-shadow: 0 6px 25px rgba(30, 60, 114, 0.4); position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; border: 3px solid rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -30px; left: -30px; width: 150px; height: 150px; border: 3px solid rgba(255,255,255,0.1); border-radius: 50%;"></div>
    <h1 style="font-family: \'Bebas Neue\', \'Arial\', sans-serif; font-size: 60px; font-weight: 400; color: #ffffff; margin: 0; letter-spacing: 4px; text-transform: uppercase; position: relative; z-index: 1; text-shadow: 2px 2px 8px rgba(0,0,0,0.3);">ACHIEVEMENT</h1>
    <h2 style="font-family: \'Bebas Neue\', sans-serif; font-size: 26px; font-weight: 400; color: #e8f4f8; margin: 8px 0 0 0; letter-spacing: 3px; position: relative; z-index: 1;">CERTIFICATE OF EXCELLENCE</h2>
    <p style="font-size: 15px; color: #b8d4e3; margin-top: 15px; letter-spacing: 1.5px; position: relative; z-index: 1;">{{SCHOOL_NAME}}</p>
</div>',
        'body_html' => '<div style="text-align: center; padding: 32px 22px;">
    <p style="font-size: 20px; color: #2c3e50; margin-bottom: 26px; font-family: \'Roboto\', sans-serif; font-weight: 300;">
        This certificate is presented to
    </p>
    <div style="margin: 28px 0; padding: 26px; background: linear-gradient(135deg, #f0f4f8 0%, #d6e4f0 100%); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 2px solid #2a5298;">
        <h3 style="font-family: \'Bebas Neue\', sans-serif; font-size: 48px; font-weight: 400; color: #1e3c72; margin: 0; letter-spacing: 2px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 18px; color: #34495e; margin: 30px 0; font-family: \'Roboto\', sans-serif; line-height: 1.9; font-weight: 300;">
        in recognition of exceptional achievement and outstanding performance in<br>
        <strong style="color: #1e3c72; font-size: 20px; font-weight: 500;">{{CLASS}}</strong><br>
        during the <strong style="color: #1e3c72; font-weight: 500;">{{SESSION}}</strong> academic year
    </p>
    <div style="margin-top: 32px; display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 22px; color: white; box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);">
        <p style="font-size: 13px; margin: 5px 0; font-weight: 500;">Certificate No: {{CERTIFICATE_ID}} | Date: {{DATE}}</p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 48px; padding: 0 28px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #1e3c72; width: 175px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 600; font-size: 16px; color: #1e3c72; margin: 5px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 2px solid #1e3c72; width: 175px; margin: 0 auto 10px; padding-top: 8px;"></div>
        <p style="font-weight: 600; font-size: 16px; color: #1e3c72; margin: 5px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 22px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 0,
        'is_default' => 0
    ],

    // Template 10: Classic Custom Certificate (Portrait)
    [
        'template_name' => 'Classic Custom Certificate',
        'certificate_type' => 'custom',
        'page_orientation' => 'portrait',
        'page_size' => 'A4',
        'header_html' => '<div style="text-align: center; margin-bottom: 48px; position: relative;">
    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 18px;">
        <div style="flex: 1; height: 2px; background: linear-gradient(to right, transparent, #d4af37, transparent);"></div>
        <div style="width: 70px; height: 70px; border: 4px solid #d4af37; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 20px; background: rgba(212, 175, 55, 0.1);">
            <span style="font-size: 32px; color: #d4af37;">★</span>
        </div>
        <div style="flex: 1; height: 2px; background: linear-gradient(to left, transparent, #d4af37, transparent);"></div>
    </div>
    <h1 style="font-family: \'Libre Baskerville\', \'Times New Roman\', serif; font-size: 54px; font-weight: 700; color: #2c3e50; margin: 0; letter-spacing: 3px; text-transform: uppercase;">CERTIFICATE</h1>
    <h2 style="font-family: \'Libre Baskerville\', serif; font-size: 26px; font-weight: 400; color: #7f8c8d; margin: 12px 0 0 0; font-style: italic;">OF MERIT</h2>
    <div style="margin-top: 22px; padding-top: 18px; border-top: 2px solid #ecf0f1;">
        <p style="font-size: 15px; color: #95a5a6; letter-spacing: 1.5px;">{{SCHOOL_NAME}}</p>
    </div>
</div>',
        'body_html' => '<div style="text-align: center; padding: 32px 42px; font-family: \'Libre Baskerville\', \'Georgia\', serif;">
    <p style="font-size: 21px; color: #2c3e50; margin-bottom: 28px; line-height: 2;">
        This is to certify that
    </p>
    <div style="margin: 28px 0; padding: 24px 0; border-top: 4px double #d4af37; border-bottom: 4px double #d4af37;">
        <h3 style="font-family: \'Libre Baskerville\', serif; font-size: 45px; font-weight: 700; color: #2c3e50; margin: 0; letter-spacing: 1px;">{{STUDENT_NAME}}</h3>
    </div>
    <p style="font-size: 19px; color: #34495e; margin: 32px 0; line-height: 2;">
        has demonstrated excellence and commitment in<br>
        <strong style="color: #d4af37; font-size: 21px;">{{CLASS}}</strong><br>
        throughout the academic session <strong style="color: #d4af37;">{{SESSION}}</strong>
    </p>
    <div style="margin-top: 38px; padding: 17px; background: #fef9e7; border: 2px solid #d4af37; border-radius: 6px;">
        <p style="font-size: 14px; color: #2c3e50; margin: 6px 0;">
            <strong>Certificate Number:</strong> {{CERTIFICATE_ID}}
        </p>
        <p style="font-size: 14px; color: #2c3e50; margin: 6px 0;">
            <strong>Date of Issue:</strong> {{DATE}}
        </p>
    </div>
</div>',
        'footer_html' => '<div style="display: flex; justify-content: space-between; margin-top: 78px; padding: 0 52px;">
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #d4af37; width: 168px; margin: 0 auto 11px; padding-top: 9px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #2c3e50; margin: 7px 0;">{{PRINCIPAL_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Principal</p>
    </div>
    <div style="text-align: center; flex: 1;">
        <div style="border-top: 3px solid #d4af37; width: 168px; margin: 0 auto 11px; padding-top: 9px;"></div>
        <p style="font-weight: 700; font-size: 17px; color: #2c3e50; margin: 7px 0;">{{REGISTRAR_SIGNATURE}}</p>
        <p style="font-size: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Registrar</p>
    </div>
</div>
<div style="text-align: center; margin-top: 28px;">{{QR_CODE}}</div>',
        'signature_1_label' => 'Principal',
        'signature_2_label' => 'Registrar',
        'include_qr_code' => 1,
        'include_watermark' => 1,
        'is_default' => 0
    ]
];

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to 1 if not logged in
$successCount = 0;
$errorCount = 0;

global $conn;

try {
    $conn->begin_transaction();
    
    foreach ($templates as $index => $template) {
        // Check if template already exists
        $checkSql = "SELECT id FROM certificate_templates WHERE template_name = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('s', $template['template_name']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "⏭️  Skipping '{$template['template_name']}' - already exists\n";
            continue;
        }
        
        // Generate unique code
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $template['template_name']), 0, 20)) . '_' . ($index + 1) . '_' . time();
        $code = substr($code, 0, 50); // Ensure it fits in varchar(50)
        
        // Check if code already exists
        $codeCheckSql = "SELECT id FROM certificate_templates WHERE code = ?";
        $codeCheckStmt = $conn->prepare($codeCheckSql);
        $codeCheckStmt->bind_param('s', $code);
        $codeCheckStmt->execute();
        $codeResult = $codeCheckStmt->get_result();
        
        if ($codeResult->num_rows > 0) {
            $code = $code . '_' . rand(1000, 9999);
        }
        
        // Insert template - check if code column exists first
        $colCheck = $conn->query("SHOW COLUMNS FROM `certificate_templates` LIKE 'code'");
        if ($colCheck && $colCheck->num_rows > 0) {
            // Table has code column
            $sql = "INSERT INTO certificate_templates (
                template_name, code, certificate_type, branch_id, 
                page_orientation, page_size, header_html, body_html, footer_html, 
                signature_1_label, signature_2_label, include_qr_code, include_watermark, 
                is_default, is_active, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $branchIdNull = null;
            $stmt->bind_param(
                'sssssssssssiiii',
                $template['template_name'],
                $code,
                $template['certificate_type'],
                $branchIdNull,
                $template['page_orientation'],
                $template['page_size'],
                $template['header_html'],
                $template['body_html'],
                $template['footer_html'],
                $template['signature_1_label'],
                $template['signature_2_label'],
                $template['include_qr_code'],
                $template['include_watermark'],
                $template['is_default'],
                $userId
            );
        } else {
            // Table doesn't have code column (newer structure)
            $sql = "INSERT INTO certificate_templates (
                template_name, certificate_type, branch_id, 
                page_orientation, page_size, header_html, body_html, footer_html, 
                signature_1_label, signature_2_label, include_qr_code, include_watermark, 
                is_default, is_active, created_by, created_at
            ) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sssssssssiiii',
                $template['template_name'],
                $template['certificate_type'],
                $template['page_orientation'],
                $template['page_size'],
                $template['header_html'],
                $template['body_html'],
                $template['footer_html'],
                $template['signature_1_label'],
                $template['signature_2_label'],
                $template['include_qr_code'],
                $template['include_watermark'],
                $template['is_default'],
                $userId
            );
        }
        
        if ($stmt->execute()) {
            $successCount++;
            echo "✅ Created: {$template['template_name']} ({$template['certificate_type']})\n";
        } else {
            $errorCount++;
            echo "❌ Error creating: {$template['template_name']} - " . $stmt->error . "\n";
        }
        
        $stmt->close();
    }
    
    $conn->commit();
    
    echo "\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "✨ SUMMARY\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "✅ Successfully created: $successCount templates\n";
    if ($errorCount > 0) {
        echo "❌ Errors: $errorCount templates\n";
    }
    echo "═══════════════════════════════════════════════════\n";
    echo "\n🎉 All beautiful certificate templates have been created!\n";
    echo "You can now view them in: Certificates > Certificate Templates\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";

if (php_sapi_name() !== 'cli') {
    echo "<br><br><a href='" . APP_URL . "modules/certificates/templates.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>View Templates</a>";
}

