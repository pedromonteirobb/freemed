<?php
 // $Id$
 // HL7 v3 CDA Generation Class
 //
 // Authors:
 //      Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // FreeMED Electronic Medical Record and Practice Management System
 // Copyright (C) 1999-2009 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

// Class: org.freemedsoftware.core.HL7v3_CDA
//
//	HL7 v3 CDA generation superclass
//
class HL7v3_CDA {

	private $patient;
	private $patient_record;

	protected $typeid = array (
		'root' => '2.16.840.1.113883.1.3',
		'extension' => 'POCD_HD000040'
	);
	protected $EOL = "\n";

	// Variable: $code
	//
	//	Describe code information for current document.
	//	Consists of a hash with the following keys:
	//	* code
	//	* codeSystem
	//	* codeSystemName
	//	* displayName
	//
	protected $code;

	public function __construct ( ) { }

	// Method: Generate
	//
	//	Public method to generate a message.
	//
	// Parameters:
	//
	//	$id - Id for record to be generated, if applicable.
	//
	// Returns:
	//
	//	XML formatted text.
	//
	public function Generate ( $id = 0 ) {
		$buffer .= '<' . '?xml version="1.0" ?' . '>' . $this->EOL;
		$buffer .= '<!-- Generated by ' . PACKAGENAME . ' v' . VERSION.' -->' . $this->EOL;
		$buffer .= '<ClinicalDocument xmlns="urn:hl7-org:v3" xmlns:mif="urn:hl7-org:v3/mif" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . $this->EOL;
		$buffer .= '     <typeId root="' . $this->typeid['root'] . '" extension="' . $this->typeid['extension'] . '" />'. $this->EOL;
		$buffer .= '     <id root="' . $this->GetHL7Id( ) . '" extension="' . $this->DocumentUUID( get_class($this), $id ) . '" />' . $this->EOL;
		$buffer .= '     <code code="' . $this->code['code'] . '" codeSystem="' . $this->code['codeSystem'] . '" codeSystemName="' . $this->code['codeSystemName'] . '" displayName="' . $this->code['displayName'] . '" />' . $this->EOL;
		$buffer .= '     <effectiveTime value="' . $this->TimestampToISO8601() . '" />' . $this->EOL;
		$buffer .= $this->GenerateRecordTarget ( );
		$buffer .= $this->GeneratePastMedicalHistoryComponent ( );
		$buffer .= $this->GenerateBody( $id );
		$buffer .= '</ClinicalDocument>' . $this->EOL;

		return $buffer;
	} // end method Generate

	// Method: GenerateBody
	//
	//	Interface/abstract method meant to allow children classes
	//	to generate their own CDA message bodies.
	//
	// Parameters:
	//
	//	$id - Internal id number, if required.
	//
	// Returns:
	//
	//	XML formatted text.
	//
	protected function GenerateBody ( $id = 0 ) {
		return '';
	} // end method GenerateBody

	protected function GenerateRecordTarget ( ) {
		$buffer .= '<recordTarget>' . $this->EOL;
		$buffer .= '  <patientRole>' . $this->EOL;
		$buffer .= '    <id root="2.16.840.1.113883.3.933" extension="' . $this->patient . '" />' . $this->EOL;
		$buffer .= $this->EncodeAddress (
				$this->patient_record['ptaddr1'],
				$this->patient_record['ptcity'],
				$this->patient_record['ptstate'],
				$this->patient_record['ptzip'],
				$this->patient_record['ptcountry']
			);

		// Figure gender
		switch (strtolower($this->patient_record['ptsex'])) {
			case 'm': $gender = 'M'; break;
			case 'f': $gender = 'F'; break;
			case 't': $gender = 'UN'; break;
			default: $gender = ''; break;
		}

		// Figure marital status
		switch (strtolower($this->patient_record['pt'])) {
			// TODO: figure this all out
			default: $marital = ''; break;
		}

		// Encode phone numbers
		$buffer .= $this->EncodeTelephone( $this->patient_record['pthphone'], 'HP' );
		$buffer .= $this->EncodeTelephone( $this->patient_record['ptwphone'], 'WP' );
		$buffer .= '    <patient>' . $this->EOL;
		$buffer .= '      <name>' . $this->EOL;
		$buffer .= '        <prefix>' . $this->patient_record['ptsalut'] . '</prefix>' . $this->EOL;
		$buffer .= '        <given>' . $this->XmlEncode( $this->patient_record['ptfname'] ) . '</given>' . $this->EOL;
		$buffer .= '        <family>' . $this->XmlEncode( $this->patient_record['ptlname'] ) . '</family>' . $this->EOL;
		$buffer .= '      </name>' . $this->EOL;
		$buffer .= '      <administrativeGenderCode code="' . $gender . '" codeSystem="2.16.840.1.113883.5.1" />' . $this->EOL;
		// TODO: reenable this: $buffer .= '      <maritalStatusCode code="' . $marital . '" codeSystem="2.16.840.1.113883.5.2" />' . $this->EOL;
		$buffer .= '      <birthTime value="' . $this->XmlEncode( str_replace ( '-', '', $this->patient_record['ptdob'] ) ) . '" />' . $this->EOL;
		$buffer .= '    </patient>' . $this->EOL;
		// TODO: providerOrganization (p15/33)
		$buffer .= '  </patientRole>' . $this->EOL;
		$buffer .= '</recordTarget>' . $this->EOL;

		return $buffer;
	} // end method GenerateRecordTarget

	// Method: GeneratePastMedicalHistoryComponent
	protected function GeneratePastMedicalHistoryComponent ( ) {
		// Get entire list from patient history
		$allDx = $GLOBALS['sql']->queryAll( "SELECT * FROM icd9 WHERE id IN ( SELECT procdiag1 FROM procrec WHERE procpatient=".$GLOBALS['sql']->quote( $this->patient )." UNION SELECT procdiag2 FROM procrec WHERE procpatient=".$GLOBALS['sql']->quote( $this->patient )." UNION SELECT procdiag3 FROM procrec WHERE procpatient=".$GLOBALS['sql']->quote( $this->patient )." UNION SELECT procdiag4 FROM procrec WHERE procpatient=".$GLOBALS['sql']->quote( $this->patient )." )" );
		if ( !count($allDx) ) { return ''; }

		$buffer .= '<pastMedicalHistoryComponent>' . $this->EOL;
		$buffer .= '  <section>' . $this->EOL;
		$buffer .= '    <code code="11348-0" codeSystem="2.16.840.1.113883.6.1" />' . $this->EOL;
		$buffer .= '    <title>Past Medical History</title>' . $this->EOL;

		foreach ( $allDx AS $dx ) {
			$buffer .= '    <entry>' . $this->EOL;
			$buffer .= '      <observation classCode="OBS" moodCode="EVN">' . $this->EOL;
			$buffer .= '        <code code="' . $dx['icd9code'] . '" codeSystem="2.16.840.1.113883.6.2" codeSystemName="ICD-9-CM" displayName="' . $dx['icd9descrip'] . '" />' . $this->EOL;
			$buffer .= '      </observation>' . $this->EOL;
			$buffer .= '    </entry>' . $this->EOL;
		}

		$buffer .= '  </section>' . $this->EOL;
		$buffer .= '</pastMedicalHistoryComponent>' . $this->EOL;

		return $buffer;
	} // end method GeneratePastMedicalHistoryComponent

	// Method: DocumentUUID
	//
	//	Generate a UUID specific for this document.
	//
	// Parameters:
	//
	//	$parent - Parent document class.
	//
	//	$id - Id specific to this document.
	//
	// Returns:
	//
	//	Hash key for identifying a document.
	//
	protected function DocumentUUID ( $parent, $id ) {
		$hash_key = ( $parent ? $parent : 'HL7v3_CDA' ) . '-' . $this->patient . '-' . ( $id ? $id : mktime() );
		return md5 ( $hash_key );
	} // end method DocumentUUID

	// Method: GetHL7Id
	//
	//	Retrieve HL7-assigned ID number
	//
	// Returns:
	//
	//	HL7-formatted ID number
	//
	protected function GetHL7Id ( ) {
		// FIXME FIXME FIXME : this is an example id - needs to be pulled from the db
		return '2.16.840.1.113883.19.4';
	} // end method GetHL7Id

	// Method: LoadPatient
	//
	//	Load patient into CDA renderer.
	//
	// Parameters:
	//
	//	$patient - Patient id
	//
	public function LoadPatient ( $patient ) {
		$this->patient = $patient;
		$this->patient_record = $GLOBALS['sql']->get_link( 'patient', $this->patient );
		if (!isset($this->patient_record['id'])) {
			die("Database connection broken\n");
		}
	} // end method LoadPatient

	// Method: TimestampToISO8601
	//
	//	Convert timestamp to CDA-compliant ISO8601 format.
	//
	// Parameters:
	//
	//	$timestamp - (optional) PHP timestamp.
	//
	// Returns:
	//
	//	CDA-compliant ISO8601 date string.
	//
	protected function TimestampToISO8601 ( $timestamp = NULL ) {
		if ($timestamp) {
			return date('YmdHisO', $timestamp);
		} else {
			return date('YmdHisO');
		}
	} // end method TimestampToISO8601

	// Method: EncodeAddress
	//
	//	CDA encode address.
	//
	// Parameters:
	//
	//	$streetaddress -
	//
	//	$city -
	//
	//	$state -
	//
	//	$postalcode -
	//
	//	$country -
	//
	// Returns:
	//
	//	XML-formatted address block
	//
	protected function EncodeAddress ( $streetaddress, $city, $state, $postalcode, $country ) {
		$buffer .= '    <addr>' . $this->EOL;
		$buffer .= '      <streetAddressLine>' . $this->XmlEncode( $streetaddress ) . '</streetAddressLine>' . $this->EOL;
		$buffer .= '      <city>' . $this->XmlEncode( $city ) . '</city>' . $this->EOL;
		$buffer .= '      <state>' . $this->XmlEncode( $state ) . '</state>' . $this->EOL;
		$buffer .= '      <postalCode>' . $this->XmlEncode( $postalcode ) . '</postalCode>' . $this->EOL;
		if ( $country ) {
			$buffer .= '      <country>' . $this->XmlEncode( $country ) . '</country>' . $this->EOL;
		}
		$buffer .= '    </addr>' . $this->EOL;

		return $buffer;
	} // end method EncodeAddress

	// Method: EncodeTelephone
	//
	//	Encode telephone/telecom number.
	//
	// Parameters:
	//
	//	$telephone - Telephone/telecom number.
	//
	//	$type - Type of phone number.
	//	* 'HP' - Home phone number
	//	* 'WP' - Work phone number
	//
	// Return:
	//
	//	XML-formatted telephone/telecom number
	//
	protected function EncodeTelephone ( $telephone, $type = '' ) {
		if ( empty($type) or empty($telephone) ) {
			return '';
		}
		return '<telecom value="tel:' . $this->XmlEncode( $telephone ) . '" use="' . $type . '" />' . $this->EOL;
	} // end method EncodeTelephone

	// Method: XmlEncode
	//
	//	Encode a string for insertion into XML.
	//
	// Parameters:
	//
	//	$string - String to encode
	//
	// Returns:
	//
	//	XML entity encoded string.
	//
	protected function XmlEncode ( $string ) {
		return htmlentities ( $string );
	} // end method XmlEncode

} // end class HL7v3_CDA

?>
