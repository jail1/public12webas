<html>
<body style="background:#f1f1f1;font-family:Arial, Helvetica, sans-serif;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid #ccc; background-color: #ffffff; background-position: center top;-webkit-box-shadow:  0px 2px 4px 0px rgba(0, 0, 0, 0.1);-moz-box-shadow:  0px 2px 4px 0px rgba(0, 0, 0, 0.1);box-shadow:  0px 2px 4px 0px rgba(0, 0, 0, 0.1);">
  <tbody style="text-align: left;">
    <tr style="text-align: left;">
      <td style="text-align: left;" valign="top"><table width="100%" border="0" cellpadding="5" cellspacing="1" style="background-color: #ffffff; width: 100%;">
          <!-- Header TR -->
          <tbody style="text-align: left;">
            <tr style="text-align: left;">
              <td style="text-align: left;border-bottom: 10px solid #A11D21;" colspan="2"><table border="0" align="left" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="height: 104px;">
                  <tbody style="text-align: left;">
                    <tr style="text-align: left;">
                      <td valign="top" style="text-align: left;"><a href="http://www.rtui.com" style="color: #0981be;" title="Register Tapes Unlimited"> <img src="http://www.rtui.com/images/logo-rtui.png" alt="Register Tapes Unlimited" width="208" height="108" style="border: 0px none; padding: 0px;" /> </a></td>
                    </tr>
                  </tbody>
                </table>
                <table border="0" align="right" cellpadding="0" cellspacing="0" >
                  <tbody>
                    <tr>
                      <td align="right" valign="middle" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-weight: 700;" >FOLLOW US:&nbsp;&nbsp;</td>
                      <td width="20" align="center" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:11px; font-weight:700;" ><a href="http://www.facebook.com/RegisterTapesUnlimited" height="16" title="Follow Register Tapes Unlimited on Facebook" width="17"><img alt="Register Tapes Unlimited Facebook" src="http://www.rtui.com/images/facebook-16x16.png" style="border-style: none; border-color: -moz-use-text-color; vertical-align: middle;" width="16" height="16" /></a></td>
                      <td width="20" align="center" valign="middle" style="font-family:Arial, Helvetica, sans-serif; font-size:11px; font-weight:700;" ><a href="http://twitter.com/RTUISocial" title="Follow Register Tapes Unlimited on Twitter"><img alt="Register Tapes Unlimited Twitter" src="http://www.rtui.com/images/twitter-16x16.png" style="border-style: none; border-color: -moz-use-text-color; vertical-align: middle;" width="16" height="16" /></a></td>
                      <td align="left" valign="middle" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-weight: 700;" >&nbsp;&nbsp;1-800-247-4793</td>
                    </tr>
                  </tbody>
                </table></td>
            </tr>
            <!-- Content TR -->
            <tr style="text-align: left;">
              <td  colspan="2" valign="middle" style="text-align: left;border-top: 5px solid #333333;">
              	<table width="100%" border="0" cellpadding="10" cellspacing="0"><tr><td><p><b><font face="Arial">RTUI Coupons - Redemption Receipt<br>
                </font></b><font face="Arial">We appreciate the confidence you have placed in us and we look forward to providing you with the best local coupons.<br>
                </font></p></td></tr></table>
              	</td>
            </tr>
            <tr style="text-align: left;">
              <td  colspan="2" bgcolor="#f1f1f1" style="text-align: left;">
                <h3><?= $coupon['name'] ?></h3>
                <a href="http://www.rtui.com/<?= $coupon['alias'] ?>.html" title="<?= $coupon['name'] ?>">
                  <img border="0" src="http://www.rtui.com/uploads/coupons/<?= $coupon['filename'] ?>" alt="<?= $coupon['name'] ?>">
                </a>
              </td>
            </tr>
<?php if ($nearby) { ?>
            <tr style="text-align: right;">
              <td colspan="2" align="right" bgcolor="#fafafa" style="text-align: right;">&nbsp;</td>
            </tr>
            <tr style="text-align: left;">
              <td bgcolor="#fafafa" style="text-align: left;"colspan="2">
                <p>
                <h4>Coupons nearby</h4><hr/>
<?php foreach ($nearby as $nc) { ?>
                <h3><?= $nc['name'] ?></h3>
                <p>
                  <a href="http://www.rtui.com/<?= $nc['alias'] ?>.html" title="<?= $nc['name'] ?>">
                    <img border="0" src="http://www.rtui.com/uploads/coupons/<?= $nc['filename'] ?>" alt="<?= $nc['name'] ?>">
                  </a>
                </p>
<?php } ?>
                </p>
              </td>
            </tr>
<?php } ?>
            <tr style="text-align: left;">
              <td height="50" colspan="2" bgcolor="#fafafa" style="text-align: left; border-top: 1px solid #A11D21; font-size: 12px; color: #333333;"><font face="Arial"size="2"><b>Find more coupons at <a href="http://www.rtui.com/free-coupons" target="_blank" style="color:#A11D21" title="Register Tapes Unlimited Testimonials">www.rtui.com/free-coupons</a></b></font></td>
            </tr>
            <tr style="text-align: center;">
              <td><table width="100%" border="0" cellpadding="5" cellspacing="0">
                  <tr>
                    <td colspan="2" bgcolor="#333333" style="text-align: left; border-top: 3px solid #A11D21; font-size: 12px; color: #FFF;"><font face="Arial"> <strong>Corporate Office:</strong> Register Tapes Unlimited &bull; 1445 Langham Creek, Houston, TX 77084 &bull; (<a href="http://www.rtui.com" target="_blank"  style="color:#f1f1f1" title="Register Tapes Unlimited">www.rtui.com</a>)</font></td>
                  </tr>
                  <tr bgcolor="#666666" style="text-align: left;">
                    <td colspan="2" style="text-align: left; font-size: 12px; color: #FFF;"><br />
                      <font face="Arial"><small>Register Tapes Unlimited (RTUI) cares about the privacy of your personal information as much as you do. <br>
                      For more information on how RTUI protects your privacy, steps you can take to protect your personal information and alerts on current privacy risks facing consumers, please visit our <a href="http://www.rtui.com/privacy-policy" style="color:#f1f1f1" title="privacy-policy" target="_blank">privacy policy page</a>.</small></font></td>
                  </tr>
                </table></td>
            </tr>
          </tbody>
        </table></td>
    </tr>
  </tbody>
</table>
</body>
</html>
