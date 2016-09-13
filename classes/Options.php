<?php
/**
 * Author: Kazumichi Yamamoto
 */

namespace Wp_Sacloud_Ojs;


class Options
{
    const SACLOUD_OJS_OPTIONS_KEY = "sacloudojs-options";
    const SACLOUD_OJS_ENDPOINT_HOSTNAME = "b.sakurastorage.jp";
    const SACLOUD_OJS_CACHED_ENDPOINT_HOSTNAME = "c.sakurastorage.jp";

    /**************************************************************************
     * Fields
     *************************************************************************/
    public $AccessKey;
    public $Secret;
    public $Bucket;
    public $UseSSL;
    public $EndpointHost;
    public $UseCache;
    public $Container;
    public $DeleteObject;

    /**************************************************************************
     * static functions
     *************************************************************************/
    /**
     * WPへオプションの登録を行います
     * @uses register_setting , add_action
     */
    public static function init()
    {
        self::load();
        register_setting(self::SACLOUD_OJS_OPTIONS_KEY, self::SACLOUD_OJS_OPTIONS_KEY, array(self::$Instance, "sanitizeOptions"));

        add_action('add_option_' . self::SACLOUD_OJS_OPTIONS_KEY, array(get_called_class(), "load"));
        add_action('update_option_' . self::SACLOUD_OJS_OPTIONS_KEY, array(get_called_class(), "load"));
        add_action('delete_option_' . self::SACLOUD_OJS_OPTIONS_KEY, array(get_called_class(), "load"));
    }

    /**
     * WPオプションテーブルから値をロードし、Staticインスタンス($Instance)を構築します。
     * @uses get_option
     */
    public static function load()
    {
        $options = get_option(self::SACLOUD_OJS_OPTIONS_KEY);
        self::$Instance = new Options($options);

    }

    public static $Instance;


    public static function deactivate()
    {
        delete_option(self::SACLOUD_OJS_OPTIONS_KEY);
    }

    /**************************************************************************
     * member functions
     *************************************************************************/

    /**
     * コンストラクタ
     * @param $values
     */
    public function __construct($values)
    {
        if (empty($values)) {
            $values = $this->defaultValues();
        }
        $this->deserializeValues($values);
    }

    /**
     * 現在の値をWPデータベースへ保存する
     * @uses update_options
     * @return void
     */
    public function save()
    {
        update_option(SELF::SACLOUD_OJS_OPTIONS_KEY, $this->serializeValues());
    }


    public function getObjectURLByName($objectName)
    {

        $bucket = $this->Bucket;
        $pref = $this->UseSSL === '1' ? "https://" : "http://";
        $host = $this->EndpointHost;
        if ($this->UseCache === '1') {
            $host = $this->Bucket . "." . self::SACLOUD_OJS_CACHED_ENDPOINT_HOSTNAME;
            $bucket = "";
        }

        if ($bucket != "") {
            $bucket .= "/";
        }

        return $pref . $host . '/' . $bucket . $objectName;
    }

    public function getObjectStorageHostURL()
    {
        $pref = $this->UseSSL === '1' ? "https://" : "http://";
        $host = $this->EndpointHost;
        if ($this->UseCache === '1') {
            $host = $this->Bucket . "." . self::SACLOUD_OJS_CACHED_ENDPOINT_HOSTNAME;
        }

        return $pref . $host;

    }

    /**
     * Option値のサニタイズ/バリデーション
     * @param $values
     * @return array|デフォルト値連想配列
     * @uses add_settings_error , sanitize_text_field
     */
    public function sanitizeOptions($values)
    {
        $defaultValues = $this->defaultValues();
        if (!is_array($values)) {
            return $defaultValues;
        }

        $apiTokensMaxLen = 40;
        $ignore_keys = array(
            'EndpointHost', 'Container'
        );
        $checkboxes = array(
            'UseSSL', 'UseCache', 'DeleteObject'
        );

        $out = array();
        foreach ($defaultValues as $key => $value) {
            if (empty ($values[$key])) {
                $out[$key] = $value;
                if ($key === 'AccessKey' && function_exists("add_settings_error")) {
                    add_settings_error(
                        self::SACLOUD_OJS_OPTIONS_KEY,
                        $key,
                        sprintf(__("%s is required.", "wp-sacloud-ojs"), __('AccessKey', 'wp-sacloud-ojs'))
                    );
                } else if ($key === 'Secret' && function_exists("add_settings_error")) {
                    add_settings_error(
                        self::SACLOUD_OJS_OPTIONS_KEY,
                        $key,
                        sprintf(__("%s is required.", "wp-sacloud-ojs"), __('Secret', 'wp-sacloud-ojs'))
                    );
                } else if ($key === 'Bucket' && function_exists("add_settings_error")) {
                    add_settings_error(
                        self::SACLOUD_OJS_OPTIONS_KEY,
                        $key,
                        sprintf(__("%s is required.", "wp-sacloud-ojs"), __('Bucket', 'wp-sacloud-ojs'))
                    );
                }else if ($key === 'DeleteObject'){
                    $out[$key] = '0';
                }
            } else {
                if (in_array($key, $ignore_keys)) {
                    // ignore keys
                    $out [$key] = $value;
                } else if (in_array($key, $checkboxes)) {
                    // if $key is in $checkboxes , set value to 1(ignore posted value)
                    $out [$key] = '1';
                } else {

                    if ('AccessKey' === $key) {
                        $value_len = strlen($values[$key]);
                        if ($value_len > $apiTokensMaxLen) {
                            add_settings_error(
                                self::SACLOUD_OJS_OPTIONS_KEY,
                                $key,
                                sprintf(__("%s is too long.", "wp-sacloud-ojs"), __('AccessKey', 'wp-sacloud-ojs'))
                            );
                            $out [$key] = $value;
                        } else {
                            $out[$key] = sanitize_text_field($values[$key]);
                        }

                    } else if ('Secret' === $key) {
                        $value_len = strlen($values[$key]);
                        if ($value_len > $apiTokensMaxLen) {
                            add_settings_error(
                                self::SACLOUD_OJS_OPTIONS_KEY,
                                $key,
                                sprintf(__("%s is too long.", "wp-sacloud-ojs"), __('Secret', 'wp-sacloud-ojs'))
                            );
                            $out [$key] = $value;
                        } else {
                            $out[$key] = sanitize_text_field($values[$key]);
                        }
                    } else if ('Bucket' === $key) {
                        $value_len = strlen($values[$key]);
                        if ($value_len > $apiTokensMaxLen) {
                            add_settings_error(
                                self::SACLOUD_OJS_OPTIONS_KEY,
                                $key,
                                sprintf(__("%s is too long.", "wp-sacloud-ojs"), __('Bucket', 'wp-sacloud-ojs'))
                            );
                            $out [$key] = $value;
                        } else {
                            $out[$key] = sanitize_text_field($values[$key]);
                        }

                    } else {
                        $out[$key] = $values[$key];
                    }
                }
            }
        }
        if (strlen($out['AccessKey']) > 0 && strlen($out['Secret']) > 0 && strlen($out['Bucket']) > 0) {
            $auth = sacloudojs_client_auth($out['AccessKey'], $out['Secret'], $out['Bucket'], $out['UseSSL'] , true);
            if (!$auth) {
                add_settings_error(
                    self::SACLOUD_OJS_OPTIONS_KEY,
                    'API_Token_Authentication_error',
                    __("API Token Authentication error", "wp-sacloud-ojs")
                );
            }
        }

        return $out;

    }

    /**************************************************************************
     * internal functions
     *************************************************************************/

    /**
     * 各設定値のデフォルト値を連想配列で返す
     * @return デフォルト値連想配列
     */
    protected
    function defaultValues()
    {
        return array(
            'AccessKey' => '',
            'Secret' => '',
            'Bucket' => '',
            'UseSSL' => '0',
            'EndpointHost' => self::SACLOUD_OJS_ENDPOINT_HOSTNAME,
            'UseCache' => '0',
            'Container' => '',
            'DeleteObject' => '1',
        );
    }

    /**
     * 連想配列からパラメータを展開しインスタンスへ設定(デシリアライズ)します
     * @param $values
     * @return void
     */
    protected
    function deserializeValues($values)
    {
        $this->AccessKey = @$values['AccessKey'];
        $this->Secret = @$values['Secret'];
        $this->Bucket = @$values['Bucket'];
        $this->UseSSL = @$values['UseSSL'];
        $this->EndpointHost = @$values['EndpointHost'];
        $this->UseCache = @$values['UseCache'];
        $this->Container = @$values['Container'];
        $this->DeleteObject = @$values['DeleteObject'];
    }

    /**
     * インスタンスから連想配列へシリアライズします
     * @return array
     */
    protected
    function serializeValues()
    {
        return array(
            'AccessKey' => $this->AccessKey,
            'Secret' => $this->Secret,
            'Bucket' => $this->Bucket,
            'UseSSL' => $this->UseSSL,
            'EndpointHost' => $this->EndpointHost,
            'UseCache' => $this->UseCache,
            'Container' => $this->Container,
            'DeleteObject' => $this->DeleteObject,
        );
    }

}