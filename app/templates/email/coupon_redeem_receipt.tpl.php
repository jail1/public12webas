<html>
<meta name=“viewport” content="initial-scale=1.0">
<body style="background:#fff;font-family:Arial, Helvetica, sans-serif; width: 730px;">
<table width="730" border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; background-position: center top;-webkit-box-shadow:  0px 2px 4px 0px rgba(0, 0, 0, 0.1);-moz-box-shadow:  0px 2px 4px 0px rgba(0, 0, 0, 0.1);box-shadow:  0px 2px 4px 0px rgba(0, 0, 0, 0.1);">
  <tbody style="text-align: left;">
    <tr style="text-align: left;">
      <td style="text-align: left;" valign="top">
      
      <table width="100%" border="0" cellpadding="5" cellspacing="0" style="background-color: #ffffff; width: 100%;">
          <!-- Header TR -->
          <tbody style="text-align: left;">
            <tr style="text-align: left;">
              <td colspan="2">
                <img src="http://www.rtui.com/images/signature/mail-header.png" alt="Register Tapes Unlimited Facebook" width="720" height="67" usemap="#Map2" style="border: 0px none; padding: 0px;" border="0" />
              </td>
            </tr>
            <!-- Content TR -->
            <tr style="text-align: left;">
              <td  colspan="2" valign="middle" style="text-align: left;">
                <table width="100%" border="0" cellpadding="5" cellspacing="0">
                  <tr>
                    <td height="50" valign="middle">
                      <font face="Arial" style="color:#125474;font-size:16px;font-weight:bold;">RTUI Coupons - Redemption Receipt</font>
                      <br>
                      <font face="Arial" style="font-size:14px; color:#848484">We appreciate the confidence you have placed in us and we look forward to providing you with the best local coupons.</font>
                    </td>
                    <td align="right" bgcolor="#ffc100" style="border-left:2px solid #125474" width="150">
                      <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td align="center">   
                            <img src="http://www.rtui.com/images/signature/emailphone.png" alt="Register Tapes Unlimited" style="border: 0px none; padding: 0px;" /><br/>
                            <span style="font-family: Arial, Helvetica, sans-serif; font-size: 13px; font-weight: 700;color:#000000">1-800-247-4793</span>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
                </td>
            </tr>
            <tr style="text-align: left;">
              <td colspan="2" style="text-align: left;">
                <a href="http://www.rtui.com/<?= $coupon['alias'] ?>.html" title="<?= $coupon['name'] ?>"><img border="0" src="http://www.rtui.com/uploads/coupons/<?= $coupon['filename'] ?>" alt="<?= $coupon['name'] ?>"></a>
              </td>              
            </tr>
            <tr style="text-align: left;">
              <td colspan="2" style="text-align: center;">
                Follow us on&nbsp;
                <a href="http://www.facebook.com/RegisterTapesUnlimited" height="16" title="Follow Register Tapes Unlimited on Facebook" width="17"><img alt="Register Tapes Unlimited Facebook" src="http://www.rtui.com/images/signature/facebook30.jpg" style="border-style: none; border-color: -moz-use-text-color; vertical-align: middle;" width="30" height="30" /></a>
                &nbsp;
                <a href="http://twitter.com/RTUISocial" title="Follow Register Tapes Unlimited on Twitter"><img alt="Register Tapes Unlimited Twitter" src="http://www.rtui.com/images/signature/twitter30.jpg" style="border-style: none; border-color: -moz-use-text-color; vertical-align: middle;" width="30" height="30" /></a>
              </td>              
            </tr>

            <?php if ($nearby) { ?>
            <tr style="text-align: right;">
              <td colspan="2" align="right" bgcolor="#fafafa" style="text-align: right;">&nbsp;</td>
            </tr>
            <tr>
              <td height="35" bgcolor="#BD141B" style="text-align: left; color: #fff;border-radius: 10px 0px 0 0;"><b>Coupons others enjoyed!</b></td>
              <td width="50%" align="right" bgcolor="#BD141B" style="color: #fff; font-size: 12px;border-radius: 0px 10px 0 0;">Find <span style="text-align: right"></span>more coupons at <a href="http://www.rtui.com/free-coupons.html" title="RTUI Free Coupons" target="_blank" style="color: #fff; text-align: right;"><b>www.rtui.com/free-coupons.html</b></a></td>
            </tr>
            <tr style="text-align: left;">
              <td bgcolor="#fafafa" style="text-align: left;border-top: 5px solid #FAE7E9;"colspan="2">
              	<?php foreach ($nearby as $nc) { ?>
                 	  <a href="http://www.rtui.com/<?= $nc['alias'] ?>.html" title="<?= $nc['name'] ?>">
                    <img border="0" src="http://www.rtui.com/uploads/coupons/<?= $nc['filename'] ?>" alt="<?= $nc['name'] ?>">
                    </a>
                <?php } ?>
              	</td>
            </tr>
            <?php } ?>
            <tr><td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">            
            <tr>
              <td rowspan="2">
              <img src="http://www.rtui.com/images/email-footer/rtui-footer-left.png" alt="Register Tapes Unlimited - Coupons"  style="border: 0px none;" />
              </td>
              <td><a href="https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=735469749&mt=8" target="_blank"><img src="http://www.rtui.com/images/email-footer/apple-app.png" alt="RTUI Coupons - iOS Version"  style="border: 0px none;" /></a></td>
              <td><a href="https://play.google.com/store/apps/details?id=ro.headlight.RTUI&hl=us" target="_blank"><img src="http://www.rtui.com/images/email-footer/google-app.png" alt="RTUI Coupons - Android Version"  style="border: 0px none;" /></a></td>
            </tr>
            <tr>
              <td colspan="2"><a href="http://www.facebook.com/RegisterTapesUnlimited" target="_blank"><img src="http://www.rtui.com/images/email-footer/facebook-like.png" alt="Register Tapes Unlimited Facebook"  style="border: 0px none;" /></a></td>
            </tr>
              </table>
            </td></tr>
            <tr style="text-align: left;">
              <td height="50" colspan="2" bgcolor="#fafafa" style="text-align: left; border-top: 1px solid #A11D21; font-size: 12px; color: #333333;"><font face="Arial"size="2"><b>Find more coupons at <a href="http://www.rtui.com/free-coupons.html" target="_blank" style="color:#A11D21" title="Register Tapes Unlimited Testimonials"><b>www.rtui.com/free-coupons.html</b></a></b></font></td>
            </tr>
            <tr style="text-align: center;">
              <td colspan="2"><table width="100%" border="0" cellpadding="5" cellspacing="0">
                  <tr>
                    <td colspan="2" align="center" valign="middle" bgcolor="#BD141B" style="text-align: center; border-top: 3px solid #FAE7E9; font-size: 12px; color: #FFF;"><font face="Arial"> <strong>Corporate Office:</strong> Register Tapes Unlimited &bull; 1445 Langham Creek, Houston, TX 77084 &bull; (<a href="http://www.rtui.com" target="_blank"  style="color:#f1f1f1" title="Register Tapes Unlimited">www.rtui.com</a>)</font><span style="text-align: center"></span><span style="text-align: right"></span></td>
                  </tr>
                  <tr style="text-align: left;">
                    <td colspan="2" style="text-align: center; font-size: 11px; "><font face="Arial"><small>Register Tapes Unlimited (RTUI) cares about the privacy of your personal information as much as you do. <br>
                      For more information on how RTUI protects your privacy, steps you can take to protect your personal information and alerts on current privacy risks facing consumers, please visit our <a href="http://www.rtui.com/rtui-privacy-policy.html" style="color:#BD141B" title="privacy-policy" target="_blank">privacy policy page</a>.</small></font></td>
                  </tr>
              </table></td>
            </tr>
          </tbody>
      </table></td>
    </tr>
  </tbody>
</table>
<map name="Map2">
  <area shape="rect" coords="3,0,129,60" href="http://www.rtui.com" target="_blank" alt="Register Tapes Unlimited">
  <area shape="rect" coords="635,1,669,61" href="http://www.facebook.com/RegisterTapesUnlimited" target="_blank" alt="Register Tapes Unlimited Facebook">
  <area shape="rect" coords="677,1,708,61" href="http://twitter.com/RTUISocial" target="_blank" alt="Register Tapes Unlimited Twitter">
</map>
</body>
</html>