<?php
/*
	WPPIZZA : css for grid based template

	this css gets loaded after and in conjunction with the responsive.css
	overriding declarations needed for grid layout

	if you want to change it directly , copy it to your theme's directory to not loose any chengs on updates

	alternatively, either create a wppizza-custom.css in your theme's directory and override declarations as required
	or - if your theme supports it - add your custom css wherever appropriate
*/
/**get / set headers, grid variables etc **/
require('../inc/frontend.require.grid-css.inc.php');
?>
/****************************************************************************************
*
*
*
*	[menu items loop - as grid - layout]
*
*
*
****************************************************************************************/
/*******************
	[grid sections/grouping]
*******************/
.wppizza-grid-section{border:none;clear: both;padding: 0px !important;margin: 0px !important;margin-bottom: 2px !important;display: -webkit-flex;display: -ms-flexbox;display: flex; }
.wppizza-grid-section:before,.wppizza-grid-section:after { content:""; display:table; }
.wppizza-grid-section:after { clear:both;}
.wppizza-grid-section { zoom:1; /* For IE 6/7 */ }
/*******************
	[menu item (article)]
*******************/
article.wppizza-article, article.wppizza-article-clear{	display: block;	float:left;	margin: 1% 0 1% <?php echo $margin ?>% !important;	padding:0 !important;	width: <?php echo $colwidth ?>% ;flex:1; border:1px dotted #CECECE;	border-radius: 5px;}
article.wppizza-article:first-child{margin-left: 0 !important; }
/*******************
	[dummies]
*******************/
article.wppizza-article-clear{border-color:transparent !important; background:transparent !important;}
/*******************
	[menu item (article) inner - wraps to actual content]
*******************/
.wppizza-article-inner{padding:2px;}
/*******************
	[menu item title]
*******************/
h2.wppizza-article-title{padding-bottom:0;margin:0;text-align:center}
/*******************
	[thumbnails]
*******************/
.wppizza-article-img{text-align:center;float:none}
.wppizza-article-img .wppizza-article-img-thumb{padding:5px;margin:0 auto !important;border:1px dotted #CECECE;width:auto;max-width:none !important}
.wppizza-article-img .wppizza-article-img-placeholder{padding:5px;margin:0 auto !important;border:1px dotted #CECECE;width:75px;height:75px;background:url('img/no_image.png') center center no-repeat;}
/*******************
	[additives]
*******************/
.wppizza-article-additives-wrap{margin-left:5px}
.wppizza-article-additives{font-size:60%;margin:0;font-weight:normal}
.wppizza-article-additives>span{cursor:pointer}
.wppizza-article-additives:before{content:'*'}
/*******************
	[prices and labels]
*******************/
/*prices wrap*/
.wppizza-article-tiers{position:relative;margin:0 auto;overflow:auto;text-align:center;float:none}
/*ul prices and currency symbol*/
.wppizza-article-tiers>ul{list-style-type:none; padding:0px; margin: 0px auto; display:inline-block;}
.wppizza-article-tiers>ul>li{margin:0 !important;line-height:normal;display:table-cell;}
/*prices list*/
.wppizza-article-prices>ul{list-style-type:none !important; padding:0px; margin: 0px auto; display:inline-block;}
.wppizza-article-prices>ul>li{float:left;margin:0!important;line-height:normal;display:inline-block;}
/*prices*/
.wppizza-article-price{text-align:center;padding:3px;font-size:120%;}
.wppizza-article-price:hover{cursor:pointer;}
.wppizza-article-price>span{margin:0 !important;padding:0;display:inline !important;}
.wppizza-article-price>span:hover{text-decoration:underline}
.wppizza-article-price-lbl{font-size:60%;text-align:center;white-space: nowrap;}
.wppizza-article-price-lbl:hover{text-decoration:underline}
.wppizza-article-price-lbl:after{content: url('img/cart-black-12-12.png');position: relative;top: 2px;margin-left: 2px;}
/*large currency symbol*/
.wppizza-article-price-currency{font-size:160%;padding:0 10px !important;text-align:center;vertical-align:middle}
/*******************
	[text/content]
*******************/
.wppizza-article-info{font-size:80%;text-align:justify}
.wppizza-article-info p{margin:0}
.wppizza-article-info h2,.wppizza-article-info h3{display:inline;display:inline-block;padding-bottom:0;margin:0}
/***************************************
*
*	[media query]
*	GO FULL WIDTH BELOW xx PIXELS
*
***************************************/
@media only screen and (max-width: <?php echo"".$fullwidth."" ?>px) {
	.wppizza-grid-section{display:block;}
	.wppizza-article {margin: 1% auto !important; width: 100%!important;}
}