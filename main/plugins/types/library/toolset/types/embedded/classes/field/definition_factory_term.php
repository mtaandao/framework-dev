<?php

/**
 * Factory for term field definitions.
 */
final class MNCF_Field_Definition_Factory_Term extends MNCF_Field_Definition_Factory {

	/**
	 * Name of the option used to store term field definitions.
	 */
	const FIELD_DEFINITIONS_OPTION = 'mncf-termmeta';


	protected function get_option_name() {
		return self::FIELD_DEFINITIONS_OPTION;
	}


	protected function get_class_name() {
		return 'MNCF_Field_Definition_Term';
	}

	
	/**
	 * @return string[] All existing meta keys within the domain (= term meta).
	 */
	protected function get_existing_meta_keys() {
		global $mndb;

		$meta_keys = $mndb->get_col(
			"SELECT meta_key FROM {$mndb->termmeta} GROUP BY meta_key HAVING meta_key NOT LIKE '\_%' ORDER BY meta_key"
		);

		return $meta_keys;
	}


	/**
	 * @inheritdoc
	 * @return Types_Field_Group_Post_Factory
	 * @since 2.0
	 */
	public function get_group_factory() {
		return Types_Field_Group_Term_Factory::get_instance();
	}


	/**
	 * @inheritdoc
	 * @return string
	 * @since 2.0
	 */
	public function get_domain() {
		return Types_Field_Utils::DOMAIN_TERMS;
	}

}