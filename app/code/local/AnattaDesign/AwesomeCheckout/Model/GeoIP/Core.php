<?php

/* geoip.inc
 *
 * Copyright (C) 2007 MaxMind LLC
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

/**
 * Awesome Checkout - Magento Extension
 *
 * @package     AwesomeCheckout
 * @category    AnattaDesign
 * @copyright   Copyright 2012 AnattaDesign (http://www.anattadesign.com)
 * @version:    0.0.2
 */
class AnattaDesign_AwesomeCheckout_Model_GeoIP_Core {

	const GEOIP_COUNTRY_BEGIN = 16776960;
	const GEOIP_STATE_BEGIN_REV0 = 16700000;
	const GEOIP_STATE_BEGIN_REV1 = 16000000;
	const GEOIP_STANDARD = 0;
	const GEOIP_MEMORY_CACHE = 1;
	const GEOIP_SHARED_MEMORY = 2;
	const STRUCTURE_INFO_MAX_SIZE = 20;
	const DATABASE_INFO_MAX_SIZE = 100;
	const GEOIP_COUNTRY_EDITION = 106;
	const GEOIP_PROXY_EDITION = 8;
	const GEOIP_ASNUM_EDITION = 9;
	const GEOIP_NETSPEED_EDITION = 10;
	const GEOIP_REGION_EDITION_REV0 = 112;
	const GEOIP_REGION_EDITION_REV1 = 3;
	const GEOIP_CITY_EDITION_REV0 = 111;
	const GEOIP_CITY_EDITION_REV1 = 2;
	const GEOIP_ORG_EDITION = 110;
	const GEOIP_ISP_EDITION = 4;
	const SEGMENT_RECORD_LENGTH = 3;
	const STANDARD_RECORD_LENGTH = 3;
	const ORG_RECORD_LENGTH = 4;
	const MAX_RECORD_LENGTH = 4;
	const MAX_ORG_RECORD_LENGTH = 300;
	const GEOIP_SHM_KEY = 0x4f415401;
	const US_OFFSET = 1;
	const CANADA_OFFSET = 677;
	const WORLD_OFFSET = 1353;
	const FIPS_RANGE = 360;
	const GEOIP_UNKNOWN_SPEED = 0;
	const GEOIP_DIALUP_SPEED = 1;
	const GEOIP_CABLEDSL_SPEED = 2;
	const GEOIP_CORPORATE_SPEED = 3;

	public $flags;
	public $filehandle;
	public $memory_buffer;
	public $databaseType;
	public $databaseSegments;
	public $record_length;
	public $shmid;
	public $GEOIP_COUNTRY_CODE_TO_NUMBER = array(
		"" => 0, "AP" => 1, "EU" => 2, "AD" => 3, "AE" => 4, "AF" => 5,
		"AG" => 6, "AI" => 7, "AL" => 8, "AM" => 9, "AN" => 10, "AO" => 11,
		"AQ" => 12, "AR" => 13, "AS" => 14, "AT" => 15, "AU" => 16, "AW" => 17,
		"AZ" => 18, "BA" => 19, "BB" => 20, "BD" => 21, "BE" => 22, "BF" => 23,
		"BG" => 24, "BH" => 25, "BI" => 26, "BJ" => 27, "BM" => 28, "BN" => 29,
		"BO" => 30, "BR" => 31, "BS" => 32, "BT" => 33, "BV" => 34, "BW" => 35,
		"BY" => 36, "BZ" => 37, "CA" => 38, "CC" => 39, "CD" => 40, "CF" => 41,
		"CG" => 42, "CH" => 43, "CI" => 44, "CK" => 45, "CL" => 46, "CM" => 47,
		"CN" => 48, "CO" => 49, "CR" => 50, "CU" => 51, "CV" => 52, "CX" => 53,
		"CY" => 54, "CZ" => 55, "DE" => 56, "DJ" => 57, "DK" => 58, "DM" => 59,
		"DO" => 60, "DZ" => 61, "EC" => 62, "EE" => 63, "EG" => 64, "EH" => 65,
		"ER" => 66, "ES" => 67, "ET" => 68, "FI" => 69, "FJ" => 70, "FK" => 71,
		"FM" => 72, "FO" => 73, "FR" => 74, "FX" => 75, "GA" => 76, "GB" => 77,
		"GD" => 78, "GE" => 79, "GF" => 80, "GH" => 81, "GI" => 82, "GL" => 83,
		"GM" => 84, "GN" => 85, "GP" => 86, "GQ" => 87, "GR" => 88, "GS" => 89,
		"GT" => 90, "GU" => 91, "GW" => 92, "GY" => 93, "HK" => 94, "HM" => 95,
		"HN" => 96, "HR" => 97, "HT" => 98, "HU" => 99, "ID" => 100, "IE" => 101,
		"IL" => 102, "IN" => 103, "IO" => 104, "IQ" => 105, "IR" => 106, "IS" => 107,
		"IT" => 108, "JM" => 109, "JO" => 110, "JP" => 111, "KE" => 112, "KG" => 113,
		"KH" => 114, "KI" => 115, "KM" => 116, "KN" => 117, "KP" => 118, "KR" => 119,
		"KW" => 120, "KY" => 121, "KZ" => 122, "LA" => 123, "LB" => 124, "LC" => 125,
		"LI" => 126, "LK" => 127, "LR" => 128, "LS" => 129, "LT" => 130, "LU" => 131,
		"LV" => 132, "LY" => 133, "MA" => 134, "MC" => 135, "MD" => 136, "MG" => 137,
		"MH" => 138, "MK" => 139, "ML" => 140, "MM" => 141, "MN" => 142, "MO" => 143,
		"MP" => 144, "MQ" => 145, "MR" => 146, "MS" => 147, "MT" => 148, "MU" => 149,
		"MV" => 150, "MW" => 151, "MX" => 152, "MY" => 153, "MZ" => 154, "NA" => 155,
		"NC" => 156, "NE" => 157, "NF" => 158, "NG" => 159, "NI" => 160, "NL" => 161,
		"NO" => 162, "NP" => 163, "NR" => 164, "NU" => 165, "NZ" => 166, "OM" => 167,
		"PA" => 168, "PE" => 169, "PF" => 170, "PG" => 171, "PH" => 172, "PK" => 173,
		"PL" => 174, "PM" => 175, "PN" => 176, "PR" => 177, "PS" => 178, "PT" => 179,
		"PW" => 180, "PY" => 181, "QA" => 182, "RE" => 183, "RO" => 184, "RU" => 185,
		"RW" => 186, "SA" => 187, "SB" => 188, "SC" => 189, "SD" => 190, "SE" => 191,
		"SG" => 192, "SH" => 193, "SI" => 194, "SJ" => 195, "SK" => 196, "SL" => 197,
		"SM" => 198, "SN" => 199, "SO" => 200, "SR" => 201, "ST" => 202, "SV" => 203,
		"SY" => 204, "SZ" => 205, "TC" => 206, "TD" => 207, "TF" => 208, "TG" => 209,
		"TH" => 210, "TJ" => 211, "TK" => 212, "TM" => 213, "TN" => 214, "TO" => 215,
		"TL" => 216, "TR" => 217, "TT" => 218, "TV" => 219, "TW" => 220, "TZ" => 221,
		"UA" => 222, "UG" => 223, "UM" => 224, "US" => 225, "UY" => 226, "UZ" => 227,
		"VA" => 228, "VC" => 229, "VE" => 230, "VG" => 231, "VI" => 232, "VN" => 233,
		"VU" => 234, "WF" => 235, "WS" => 236, "YE" => 237, "YT" => 238, "RS" => 239,
		"ZA" => 240, "ZM" => 241, "ME" => 242, "ZW" => 243, "A1" => 244, "A2" => 245,
		"O1" => 246, "AX" => 247, "GG" => 248, "IM" => 249, "JE" => 250, "BL" => 251,
		"MF" => 252
	);
	public $GEOIP_COUNTRY_CODES = array(
		"", "AP", "EU", "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AN", "AO", "AQ",
		"AR", "AS", "AT", "AU", "AW", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH",
		"BI", "BJ", "BM", "BN", "BO", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA",
		"CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU",
		"CV", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG",
		"EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "FX", "GA", "GB",
		"GD", "GE", "GF", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT",
		"GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IN",
		"IO", "IQ", "IR", "IS", "IT", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM",
		"KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS",
		"LT", "LU", "LV", "LY", "MA", "MC", "MD", "MG", "MH", "MK", "ML", "MM", "MN",
		"MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA",
		"NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA",
		"PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY",
		"QA", "RE", "RO", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI",
		"SJ", "SK", "SL", "SM", "SN", "SO", "SR", "ST", "SV", "SY", "SZ", "TC", "TD",
		"TF", "TG", "TH", "TJ", "TK", "TM", "TN", "TO", "TL", "TR", "TT", "TV", "TW",
		"TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN",
		"VU", "WF", "WS", "YE", "YT", "RS", "ZA", "ZM", "ME", "ZW", "A1", "A2", "O1",
		"AX", "GG", "IM", "JE", "BL", "MF"
	);
	public $GEOIP_COUNTRY_CODES3 = array(
		"", "AP", "EU", "AND", "ARE", "AFG", "ATG", "AIA", "ALB", "ARM", "ANT", "AGO", "AQ", "ARG",
		"ASM", "AUT", "AUS", "ABW", "AZE", "BIH", "BRB", "BGD", "BEL", "BFA", "BGR", "BHR", "BDI",
		"BEN", "BMU", "BRN", "BOL", "BRA", "BHS", "BTN", "BV", "BWA", "BLR", "BLZ", "CAN", "CC",
		"COD", "CAF", "COG", "CHE", "CIV", "COK", "CHL", "CMR", "CHN", "COL", "CRI", "CUB", "CPV",
		"CX", "CYP", "CZE", "DEU", "DJI", "DNK", "DMA", "DOM", "DZA", "ECU", "EST", "EGY", "ESH",
		"ERI", "ESP", "ETH", "FIN", "FJI", "FLK", "FSM", "FRO", "FRA", "FX", "GAB", "GBR", "GRD",
		"GEO", "GUF", "GHA", "GIB", "GRL", "GMB", "GIN", "GLP", "GNQ", "GRC", "GS", "GTM", "GUM",
		"GNB", "GUY", "HKG", "HM", "HND", "HRV", "HTI", "HUN", "IDN", "IRL", "ISR", "IND", "IO",
		"IRQ", "IRN", "ISL", "ITA", "JAM", "JOR", "JPN", "KEN", "KGZ", "KHM", "KIR", "COM", "KNA",
		"PRK", "KOR", "KWT", "CYM", "KAZ", "LAO", "LBN", "LCA", "LIE", "LKA", "LBR", "LSO", "LTU",
		"LUX", "LVA", "LBY", "MAR", "MCO", "MDA", "MDG", "MHL", "MKD", "MLI", "MMR", "MNG", "MAC",
		"MNP", "MTQ", "MRT", "MSR", "MLT", "MUS", "MDV", "MWI", "MEX", "MYS", "MOZ", "NAM", "NCL",
		"NER", "NFK", "NGA", "NIC", "NLD", "NOR", "NPL", "NRU", "NIU", "NZL", "OMN", "PAN", "PER",
		"PYF", "PNG", "PHL", "PAK", "POL", "SPM", "PCN", "PRI", "PSE", "PRT", "PLW", "PRY", "QAT",
		"REU", "ROU", "RUS", "RWA", "SAU", "SLB", "SYC", "SDN", "SWE", "SGP", "SHN", "SVN", "SJM",
		"SVK", "SLE", "SMR", "SEN", "SOM", "SUR", "STP", "SLV", "SYR", "SWZ", "TCA", "TCD", "TF",
		"TGO", "THA", "TJK", "TKL", "TLS", "TKM", "TUN", "TON", "TUR", "TTO", "TUV", "TWN", "TZA",
		"UKR", "UGA", "UM", "USA", "URY", "UZB", "VAT", "VCT", "VEN", "VGB", "VIR", "VNM", "VUT",
		"WLF", "WSM", "YEM", "YT", "SRB", "ZAF", "ZMB", "MNE", "ZWE", "A1", "A2", "O1",
		"ALA", "GGY", "IMN", "JEY", "BLM", "MAF"
	);
	public $GEOIP_COUNTRY_NAMES = array(
		"", "Asia/Pacific Region", "Europe", "Andorra", "United Arab Emirates",
		"Afghanistan", "Antigua and Barbuda", "Anguilla", "Albania", "Armenia",
		"Netherlands Antilles", "Angola", "Antarctica", "Argentina", "American Samoa",
		"Austria", "Australia", "Aruba", "Azerbaijan", "Bosnia and Herzegovina",
		"Barbados", "Bangladesh", "Belgium", "Burkina Faso", "Bulgaria", "Bahrain",
		"Burundi", "Benin", "Bermuda", "Brunei Darussalam", "Bolivia", "Brazil",
		"Bahamas", "Bhutan", "Bouvet Island", "Botswana", "Belarus", "Belize",
		"Canada", "Cocos (Keeling) Islands", "Congo, The Democratic Republic of the",
		"Central African Republic", "Congo", "Switzerland", "Cote D'Ivoire", "Cook Islands",
		"Chile", "Cameroon", "China", "Colombia", "Costa Rica", "Cuba", "Cape Verde",
		"Christmas Island", "Cyprus", "Czech Republic", "Germany", "Djibouti",
		"Denmark", "Dominica", "Dominican Republic", "Algeria", "Ecuador", "Estonia",
		"Egypt", "Western Sahara", "Eritrea", "Spain", "Ethiopia", "Finland", "Fiji",
		"Falkland Islands (Malvinas)", "Micronesia, Federated States of", "Faroe Islands",
		"France", "France, Metropolitan", "Gabon", "United Kingdom",
		"Grenada", "Georgia", "French Guiana", "Ghana", "Gibraltar", "Greenland",
		"Gambia", "Guinea", "Guadeloupe", "Equatorial Guinea", "Greece", "South Georgia and the South Sandwich Islands",
		"Guatemala", "Guam", "Guinea-Bissau",
		"Guyana", "Hong Kong", "Heard Island and McDonald Islands", "Honduras",
		"Croatia", "Haiti", "Hungary", "Indonesia", "Ireland", "Israel", "India",
		"British Indian Ocean Territory", "Iraq", "Iran, Islamic Republic of",
		"Iceland", "Italy", "Jamaica", "Jordan", "Japan", "Kenya", "Kyrgyzstan",
		"Cambodia", "Kiribati", "Comoros", "Saint Kitts and Nevis", "Korea, Democratic People's Republic of",
		"Korea, Republic of", "Kuwait", "Cayman Islands",
		"Kazakhstan", "Lao People's Democratic Republic", "Lebanon", "Saint Lucia",
		"Liechtenstein", "Sri Lanka", "Liberia", "Lesotho", "Lithuania", "Luxembourg",
		"Latvia", "Libyan Arab Jamahiriya", "Morocco", "Monaco", "Moldova, Republic of",
		"Madagascar", "Marshall Islands", "Macedonia",
		"Mali", "Myanmar", "Mongolia", "Macau", "Northern Mariana Islands",
		"Martinique", "Mauritania", "Montserrat", "Malta", "Mauritius", "Maldives",
		"Malawi", "Mexico", "Malaysia", "Mozambique", "Namibia", "New Caledonia",
		"Niger", "Norfolk Island", "Nigeria", "Nicaragua", "Netherlands", "Norway",
		"Nepal", "Nauru", "Niue", "New Zealand", "Oman", "Panama", "Peru", "French Polynesia",
		"Papua New Guinea", "Philippines", "Pakistan", "Poland", "Saint Pierre and Miquelon",
		"Pitcairn Islands", "Puerto Rico", "Palestinian Territory",
		"Portugal", "Palau", "Paraguay", "Qatar", "Reunion", "Romania",
		"Russian Federation", "Rwanda", "Saudi Arabia", "Solomon Islands",
		"Seychelles", "Sudan", "Sweden", "Singapore", "Saint Helena", "Slovenia",
		"Svalbard and Jan Mayen", "Slovakia", "Sierra Leone", "San Marino", "Senegal",
		"Somalia", "Suriname", "Sao Tome and Principe", "El Salvador", "Syrian Arab Republic",
		"Swaziland", "Turks and Caicos Islands", "Chad", "French Southern Territories",
		"Togo", "Thailand", "Tajikistan", "Tokelau", "Turkmenistan",
		"Tunisia", "Tonga", "Timor-Leste", "Turkey", "Trinidad and Tobago", "Tuvalu",
		"Taiwan", "Tanzania, United Republic of", "Ukraine",
		"Uganda", "United States Minor Outlying Islands", "United States", "Uruguay",
		"Uzbekistan", "Holy See (Vatican City State)", "Saint Vincent and the Grenadines",
		"Venezuela", "Virgin Islands, British", "Virgin Islands, U.S.",
		"Vietnam", "Vanuatu", "Wallis and Futuna", "Samoa", "Yemen", "Mayotte",
		"Serbia", "South Africa", "Zambia", "Montenegro", "Zimbabwe",
		"Anonymous Proxy", "Satellite Provider", "Other",
		"Aland Islands", "Guernsey", "Isle of Man", "Jersey", "Saint Barthelemy", "Saint Martin"
	);
	public $GEOIP_CONTINENT_CODES = array(
		"--", "AS", "EU", "EU", "AS", "AS", "SA", "SA", "EU", "AS",
		"SA", "AF", "AN", "SA", "OC", "EU", "OC", "SA", "AS", "EU",
		"SA", "AS", "EU", "AF", "EU", "AS", "AF", "AF", "SA", "AS",
		"SA", "SA", "SA", "AS", "AF", "AF", "EU", "SA", "NA", "AS",
		"AF", "AF", "AF", "EU", "AF", "OC", "SA", "AF", "AS", "SA",
		"SA", "SA", "AF", "AS", "AS", "EU", "EU", "AF", "EU", "SA",
		"SA", "AF", "SA", "EU", "AF", "AF", "AF", "EU", "AF", "EU",
		"OC", "SA", "OC", "EU", "EU", "EU", "AF", "EU", "SA", "AS",
		"SA", "AF", "EU", "SA", "AF", "AF", "SA", "AF", "EU", "SA",
		"SA", "OC", "AF", "SA", "AS", "AF", "SA", "EU", "SA", "EU",
		"AS", "EU", "AS", "AS", "AS", "AS", "AS", "EU", "EU", "SA",
		"AS", "AS", "AF", "AS", "AS", "OC", "AF", "SA", "AS", "AS",
		"AS", "SA", "AS", "AS", "AS", "SA", "EU", "AS", "AF", "AF",
		"EU", "EU", "EU", "AF", "AF", "EU", "EU", "AF", "OC", "EU",
		"AF", "AS", "AS", "AS", "OC", "SA", "AF", "SA", "EU", "AF",
		"AS", "AF", "NA", "AS", "AF", "AF", "OC", "AF", "OC", "AF",
		"SA", "EU", "EU", "AS", "OC", "OC", "OC", "AS", "SA", "SA",
		"OC", "OC", "AS", "AS", "EU", "SA", "OC", "SA", "AS", "EU",
		"OC", "SA", "AS", "AF", "EU", "AS", "AF", "AS", "OC", "AF",
		"AF", "EU", "AS", "AF", "EU", "EU", "EU", "AF", "EU", "AF",
		"AF", "SA", "AF", "SA", "AS", "AF", "SA", "AF", "AF", "AF",
		"AS", "AS", "OC", "AS", "AF", "OC", "AS", "EU", "SA", "OC",
		"AS", "AF", "EU", "AF", "OC", "NA", "SA", "AS", "EU", "SA",
		"SA", "SA", "SA", "AS", "OC", "OC", "OC", "AS", "AF", "EU",
		"AF", "AF", "EU", "AF", "--", "--", "--", "EU", "EU", "EU",
		"EU", "SA", "SA" );

	public function geoip_load_shared_mem( $file ) {

		$fp = fopen( $file, "rb" );
		if ( !$fp ) {
			print "error opening $file: $php_errormsg\n";
			exit;
		}
		$s_array = fstat( $fp );
		$size = $s_array['size'];
		if ( $shmid = @shmop_open( self::GEOIP_SHM_KEY, "w", 0, 0 ) ) {
			shmop_delete( $shmid );
			shmop_close( $shmid );
		}
		$shmid = shmop_open( self::GEOIP_SHM_KEY, "c", 0644, $size );
		shmop_write( $shmid, fread( $fp, $size ), 0 );
		shmop_close( $shmid );
	}

	static function _setup_segments( $gi ) {
		$gi->databaseType = self::GEOIP_COUNTRY_EDITION;
		$gi->record_length = self::STANDARD_RECORD_LENGTH;
		if ( $gi->flags & self::GEOIP_SHARED_MEMORY ) {
			$offset = @shmop_size( $gi->shmid ) - 3;
			for ( $i = 0; $i < self::STRUCTURE_INFO_MAX_SIZE; $i++ ) {
				$delim = @shmop_read( $gi->shmid, $offset, 3 );
				$offset += 3;
				if ( $delim == (chr( 255 ) . chr( 255 ) . chr( 255 )) ) {
					$gi->databaseType = ord( @shmop_read( $gi->shmid, $offset, 1 ) );
					$offset++;

					if ( $gi->databaseType == self::GEOIP_REGION_EDITION_REV0 ) {
						$gi->databaseSegments = self::GEOIP_STATE_BEGIN_REV0;
					} else if ( $gi->databaseType == self::GEOIP_REGION_EDITION_REV1 ) {
						$gi->databaseSegments = self::GEOIP_STATE_BEGIN_REV1;
					} else if ( ($gi->databaseType == self::GEOIP_CITY_EDITION_REV0) ||
							($gi->databaseType == self::GEOIP_CITY_EDITION_REV1)
							|| ($gi->databaseType == self::GEOIP_ORG_EDITION)
							|| ($gi->databaseType == self::GEOIP_ISP_EDITION)
							|| ($gi->databaseType == self::GEOIP_ASNUM_EDITION) ) {
						$gi->databaseSegments = 0;
						$buf = @shmop_read( $gi->shmid, $offset, self::SEGMENT_RECORD_LENGTH );
						for ( $j = 0; $j < self::SEGMENT_RECORD_LENGTH; $j++ ) {
							$gi->databaseSegments += (ord( $buf[$j] ) << ($j * 8));
						}
						if ( ($gi->databaseType == self::GEOIP_ORG_EDITION) ||
								($gi->databaseType == self::GEOIP_ISP_EDITION) ) {
							$gi->record_length = self::ORG_RECORD_LENGTH;
						}
					}
					break;
				} else {
					$offset -= 4;
				}
			}
			if ( ($gi->databaseType == self::GEOIP_COUNTRY_EDITION) ||
					($gi->databaseType == self::GEOIP_PROXY_EDITION) ||
					($gi->databaseType == self::GEOIP_NETSPEED_EDITION) ) {
				$gi->databaseSegments = self::GEOIP_COUNTRY_BEGIN;
			}
		} else {
			$filepos = ftell( $gi->filehandle );
			fseek( $gi->filehandle, -3, SEEK_END );
			for ( $i = 0; $i < self::STRUCTURE_INFO_MAX_SIZE; $i++ ) {
				$delim = fread( $gi->filehandle, 3 );
				if ( $delim == (chr( 255 ) . chr( 255 ) . chr( 255 )) ) {
					$gi->databaseType = ord( fread( $gi->filehandle, 1 ) );
					if ( $gi->databaseType == self::GEOIP_REGION_EDITION_REV0 ) {
						$gi->databaseSegments = self::GEOIP_STATE_BEGIN_REV0;
					} else if ( $gi->databaseType == self::GEOIP_REGION_EDITION_REV1 ) {
						$gi->databaseSegments = self::GEOIP_STATE_BEGIN_REV1;
					} else if ( ($gi->databaseType == self::GEOIP_CITY_EDITION_REV0) ||
							($gi->databaseType == self::GEOIP_CITY_EDITION_REV1) ||
							($gi->databaseType == self::GEOIP_ORG_EDITION) ||
							($gi->databaseType == self::GEOIP_ISP_EDITION) ||
							($gi->databaseType == self::GEOIP_ASNUM_EDITION) ) {
						$gi->databaseSegments = 0;
						$buf = fread( $gi->filehandle, self::SEGMENT_RECORD_LENGTH );
						for ( $j = 0; $j < self::SEGMENT_RECORD_LENGTH; $j++ ) {
							$gi->databaseSegments += (ord( $buf[$j] ) << ($j * 8));
						}
						if ( $gi->databaseType == self::GEOIP_ORG_EDITION ||
								$gi->databaseType == self::GEOIP_ISP_EDITION ) {
							$gi->record_length = self::ORG_RECORD_LENGTH;
						}
					}
					break;
				} else {
					fseek( $gi->filehandle, -4, SEEK_CUR );
				}
			}
			if ( ($gi->databaseType == self::GEOIP_COUNTRY_EDITION) ||
					($gi->databaseType == self::GEOIP_PROXY_EDITION) ||
					($gi->databaseType == self::GEOIP_NETSPEED_EDITION) ) {
				$gi->databaseSegments = self::GEOIP_COUNTRY_BEGIN;
			}
			fseek( $gi->filehandle, $filepos, SEEK_SET );
		}
		return $gi;
	}

	protected function __construct() {

	}

	protected function __clone() {

	}

	protected static $gi;

	static function getInstance( $filename, $flags ) {
		if ( is_null( self::$gi ) ) {

			$gi = new AnattaDesign_AwesomeCheckout_Model_GeoIP_Core;
			$gi->flags = $flags;
			if ( $gi->flags & self::GEOIP_SHARED_MEMORY ) {
				$gi->shmid = @shmop_open( self::GEOIP_SHM_KEY, "a", 0, 0 );
			} else {
				$gi->filehandle = fopen( $filename, "rb" );

				if ( !$gi->filehandle ) {
					throw new Exception( "Can not open $filename\n" );
				}
				if ( $gi->flags & self::GEOIP_MEMORY_CACHE ) {
					$s_array = fstat( $gi->filehandle );
					$gi->memory_buffer = fread( $gi->filehandle, $s_array['size'] );
				}
			}

			$gi = self::_setup_segments( $gi );
			self::$gi = $gi;
		}
		return self::$gi;
	}

	public function geoip_close( $gi ) {
		if ( $gi->flags & self::GEOIP_SHARED_MEMORY ) {
			return true;
		}

		return fclose( $gi->filehandle );
	}

	public function geoip_country_id_by_name( $gi, $name ) {
		$addr = gethostbyname( $name );
		if ( !$addr || $addr == $name ) {
			return false;
		}
		return $this->geoip_country_id_by_addr( $gi, $addr );
	}

	public function geoip_country_code_by_name( $gi, $name ) {
		$country_id = $this->geoip_country_id_by_name( $gi, $name );
		if ( $country_id !== false ) {
			return $gi->GEOIP_COUNTRY_CODES[$country_id];
		}
		return false;
	}

	public function geoip_country_name_by_name( $gi, $name ) {
		$country_id = $this->geoip_country_id_by_name( $gi, $name );
		if ( $country_id !== false ) {
			return $gi->GEOIP_COUNTRY_NAMES[$country_id];
		}
		return false;
	}

	public function geoip_country_id_by_addr( $gi, $addr ) {
		$ipnum = ip2long( $addr );
		return $this->_geoip_seek_country( $gi, $ipnum ) - self::GEOIP_COUNTRY_BEGIN;
	}

	public function geoip_record_by_addr( $addr ) {
		return AnattaDesign_AwesomeCheckout_Model_GeoIP_City::geoip_record_by_addr( $this, $addr, $this );
	}

	public function geoip_country_code_by_addr( $addr ) {
		$gi = $this;
		if ( $gi->databaseType == self::GEOIP_CITY_EDITION_REV1 ) {
			$record = AnattaDesign_AwesomeCheckout_Model_GeoIP_City::geoip_record_by_addr( $gi, $addr, $this );
			if ( $record ) {
				return $record->country_code;
			}
		} else {
			$country_id = $this->geoip_country_id_by_addr( $gi, $addr );
			if ( $country_id !== false ) {
				return $gi->GEOIP_COUNTRY_CODES[$country_id];
			}
		}
		return false;
	}

	public function geoip_country_name_by_addr( $gi, $addr ) {
		if ( $gi->databaseType == self::GEOIP_CITY_EDITION_REV1 ) {
			$record = AnattaDesign_AwesomeCheckout_Model_GeoIP_City::geoip_record_by_addr( $gi, $addr, $this );
			return $record->country_name;
		} else {
			$country_id = geoip_country_id_by_addr( $gi, $addr );
			if ( $country_id !== false ) {
				return $gi->GEOIP_COUNTRY_NAMES[$country_id];
			}
		}
		return false;
	}

	public function _geoip_seek_country( $gi, $ipnum ) {
		$offset = 0;
		for ( $depth = 31; $depth >= 0; --$depth ) {
			if ( $gi->flags & self::GEOIP_MEMORY_CACHE ) {
				// workaround php's broken substr, strpos, etc handling with
				// mbstring.func_overload and mbstring.internal_encoding
				$enc = mb_internal_encoding();
				mb_internal_encoding( 'ISO-8859-1' );

				$buf = substr( $gi->memory_buffer, 2 * $gi->record_length * $offset, 2 * $gi->record_length );

				mb_internal_encoding( $enc );
			} elseif ( $gi->flags & self::GEOIP_SHARED_MEMORY ) {
				$buf = @shmop_read( $gi->shmid, 2 * $gi->record_length * $offset, 2 * $gi->record_length );
			} else {
				if ( !(fseek( $gi->filehandle, 2 * $gi->record_length * $offset, SEEK_SET ) == 0) ) {
					throw new Exception( "fseek failed" );
				}
				$buf = fread( $gi->filehandle, 2 * $gi->record_length );
			}
			$x = array( 0, 0 );
			for ( $i = 0; $i < 2; ++$i ) {
				for ( $j = 0; $j < $gi->record_length; ++$j ) {
					$x[$i] += ord( $buf[$gi->record_length * $i + $j] ) << ($j * 8);
				}
			}
			if ( $ipnum & (1 << $depth) ) {
				if ( $x[1] >= $gi->databaseSegments ) {
					return $x[1];
				}
				$offset = $x[1];
			} else {
				if ( $x[0] >= $gi->databaseSegments ) {
					return $x[0];
				}
				$offset = $x[0];
			}
		}
		trigger_error( "error traversing database - perhaps it is corrupt?", self::E_USER_ERROR );
		return false;
	}

	public function _get_org( $gi, $ipnum ) {
		$seek_org = $this->_geoip_seek_country( $gi, $ipnum );
		if ( $seek_org == $gi->databaseSegments ) {
			return NULL;
		}
		$record_pointer = $seek_org + (2 * $gi->record_length - 1) * $gi->databaseSegments;
		if ( $gi->flags & self::GEOIP_SHARED_MEMORY ) {
			$org_buf = @shmop_read( $gi->shmid, $record_pointer, self::MAX_ORG_RECORD_LENGTH );
		} else {
			fseek( $gi->filehandle, $record_pointer, SEEK_SET );
			$org_buf = fread( $gi->filehandle, self::MAX_ORG_RECORD_LENGTH );
		}
		// workaround php's broken substr, strpos, etc handling with
		// mbstring.func_overload and mbstring.internal_encoding
		$enc = mb_internal_encoding();
		mb_internal_encoding( 'ISO-8859-1' );
		$org_buf = substr( $org_buf, 0, strpos( $org_buf, 0 ) );
		mb_internal_encoding( $enc );
		return $org_buf;
	}

	public function geoip_org_by_addr( $gi, $addr ) {
		if ( $addr == NULL ) {
			return 0;
		}
		$ipnum = ip2long( $addr );
		return _get_org( $gi, $ipnum );
	}

	public function _get_region( $gi, $ipnum ) {


		if ( $gi->databaseType == self::GEOIP_REGION_EDITION_REV0 ) {
			$seek_region = $this->_geoip_seek_country( $gi, $ipnum ) - self::GEOIP_STATE_BEGIN_REV0;
			if ( $seek_region >= 1000 ) {
				$country_code = "US";
				$region = chr( ($seek_region - 1000) / 26 + 65 ) . chr( ($seek_region - 1000) % 26 + 65 );
			} else {


				$country_code = $gi->GEOIP_COUNTRY_CODES[$seek_region];
				$region = "";
			}
			return array( $country_code, $region );
		} else if ( $gi->databaseType == self::GEOIP_REGION_EDITION_REV1 ) {
			$seek_region = $this->_geoip_seek_country( $gi, $ipnum ) - self::GEOIP_STATE_BEGIN_REV1;
			//print $seek_region;
			if ( $seek_region < self::US_OFFSET ) {
				$country_code = "";
				$region = "";
			} else if ( $seek_region < self::CANADA_OFFSET ) {
				$country_code = "US";
				$region = chr( ($seek_region - self::US_OFFSET) / 26 + 65 ) . chr( ($seek_region - self::US_OFFSET) % 26 + 65 );
			} else if ( $seek_region < self::WORLD_OFFSET ) {
				$country_code = "CA";
				$region = chr( ($seek_region - self::CANADA_OFFSET) / 26 + 65 ) . chr( ($seek_region - self::CANADA_OFFSET) % 26 + 65 );
			} else {
				$country_code = $gi->GEOIP_COUNTRY_CODES[($seek_region - self::WORLD_OFFSET) / self::FIPS_RANGE];
				$region = "";
			}
			return array( $country_code, $region );
		}
	}

	public function geoip_region_by_addr( $gi, $addr ) {
		if ( $addr == NULL ) {
			return 0;
		}
		$ipnum = ip2long( $addr );
		return _get_region( $gi, $ipnum );
	}

	public function getdnsattributes( $l, $ip ) {
		$r = new Net_DNS_Resolver();
		$r->nameservers = array( "ws1.maxmind.com" );
		$p = $r->search( $l . "." . $ip . ".s.maxmind.com", "TXT", "IN" );
		$str = is_object( $p->answer[0] ) ? $p->answer[0]->string() : '';
		ereg( "\"(.*)\"", $str, $regs );
		$str = $regs[1];
		return $str;
	}

}