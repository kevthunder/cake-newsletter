<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Infolettre - Loi C-28</title>
	<meta name="author" content="O2 Web Solutions">
	<meta name="description" content="">
	<meta name="robots" content="all">
</head>
<body bgcolor="#f6f6f6" style="padding:0; margin:0; background: #f6f6f6">
<div style="background-color:#f6f6f6;">
	<table height="100%" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f6f6f6" style="background: #f6f6f6;">
		<tbody>
		<tr>
			<td valign="top" align="center">
				<table height="80" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="background: #ffffff;">
					<tr>
						<td>
							<table height="80" width="580" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="background: #ffffff;" align="center">
								<!-- Logo -->
								<tr >
									<td style="margin:auto;" height="100" valign="middle" align="center">
										<h1><?php echo $newsletterMaker->single(1, 'image', array()); ?></h1>
									</td>
								</tr>
								<!-- /Logo -->
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td valign="top" align="center">
				<table height="100%" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f6f6f6" style="background: #f6f6f6;">
					<tr>
						<td>
							<table  width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#f6f6f6" style="background: #f6f6f6;" align="center">
								<tr>
									<td>
									<!-- Contenu -->
									<table  width="580" cellpadding="0" cellspacing="0" border="0"  align="center">
										<font style="font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:14px; font-weight:300;">
											<?php echo $newsletterMaker->single(2,'string', array(
										'text'=>'<p>Bonjour,</p>
												<p>Nous sommes fiers de vous avoir dans notre réseau de contacts et serions heureux de 
												continuer l’aventure avec vous. Vous recevez donc ce courriel puisque vous faites 
												partie de notre liste de distribution sélective.</p>
												<p>En vertu de la nouvelle loi canadienne anti-pourriel (LCAP) qui entrera en vigueur 
												le 1er juillet 2014, nous sollicitons aujourd\'hui votre consentement afin d’être tenu 
												au courant de nos activités.</p>
												<p>Si vous acceptez de recevoir nos communications par courriel, veuillez cliquer sur le bouton ci-dessous.
												</p>')); ?></font>
										<tr>
											<td align="center" style="color:#323232;">
												<?php 
													echo $this->NewsletterMaker->single(3,'reenable_links',array(
														'link_yes'=>__d('newsletter','<img border="0" src="' . $html->url('/newsletter/img/reconfirmation/btn-oui.jpg',true) . '" width="250" height="54" alt="Oui, je souhaite rester abonné - Cliquez ici pour donner votre consentement">',true),
														'link_no'=>__d('newsletter','<img border="0" src="' . $html->url('/newsletter/img/reconfirmation/btn-non.jpg',true) . '" width="249" height="49" alt="Non, je souhaite me désabonner - Cliquez ici pour donner votre consentement">',true)
													));
												?>
											</td>
										</tr>
										<tr>
											<td align="left" style="color:#323232;">
												<font style="font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:14px; font-weight:300;">
													<?php echo $this->NewsletterMaker->single(4, 'string', array(
												'text'=>'<p>Vous pourrez à tout moment annuler votre consentement lors de nos prochains envois.</p>
													<p>Merci de votre collaboration!</p>')) ?>
													</font>
											<!-- 
												<p><font style="font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:14px; font-weight:300;">Vous pourrez à tout moment annuler votre consentement lors de nos prochains envois.</font></p>
												<p><font style="font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:14px; font-weight:300;">Merci de votre collaboration!</font></p> -->
											</td>
										</tr>
									</table>
									<!-- /Contenu -->
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table height="100" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#323232" style="background: #323232;">
								<tr>
									<td>
										<table height="100" width="580" cellpadding="0" cellspacing="0" border="0" bgcolor="#323232" style="background: #323232;" align="center">
											<!-- Footer -->
											<tr >
												<td height="80" width="90%" valign="middle" align="left">
													<font style="color:#ffffff; font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:12px; font-weight:300; letter-spacing: 0.8px;">
														<?php echo $this->NewsletterMaker->single(5,'string',array(
      														'text'=>__('<p>www.sitewebdelacompagnie.com</p>
																<p>info@sitewebdelacompagnie.com</p>
																<p>8888, Boul. NomDuBouleveard Ouest, Local 777, Québec (QC) H0H 0H0</p>',true),
      													)); ?>
													</font>
												</td>
											</tr>
											<!-- /Footer -->
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td>
							<table height="50" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="background: #ffffff;">
								<tr>
									<td>
										<table height="50" width="580" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="background: #ffffff;" align="center">
											<!-- LIRE COURRIEL -->
											<tr >
												<td width="90%" valign="middle" align="center">
													<p>
														<font style="color:#444444; font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:12px; font-weight:300; ">Si vous n’arrivez pas à lire ce courriel, </font>
														<a href="<?php echo $this->NewsletterMaker->viewUrl(true); ?>" style="color:#d52531; text-decoration: none;"><font style="color:#d52531; font-family: &#39;HelveticaNeue-Light&#39;, &#39;Helvetica Neue Light&#39;, &#39;Helvetica Neue&#39;, Helvetica, Arial, sans-serif; font-size:12px; font-weight:300;">cliquer ici</font></a>
														
													</p>
												</td>
											</tr>
											<!-- /LIRE COURRIEL -->
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</tbody>
	</table>
</div>
</body></html>