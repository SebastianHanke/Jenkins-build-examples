<?php

/**
 * Basis Model und Methoden für Device
 *
 * @package Models
 * @see AbstractDevice
 */
class Device extends AbstractDevice
{

    /**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Device the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /*
     *
     * TESTSGTDTR
     *
     *
     * */

    public function testPushAndPop()
    {
        $stack = array();
        $this->assertEquals(0, count($stack));

        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertEquals(1, count($stack));

        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }

    /**
     * Scopes ohne Parameter
     *
     * @return array scope criteria.
     */
    public function scopes()
    {
        FW::gvTrace(func_get_args(), "FunctionCall", __METHOD__, "arguments");
        return array(

            // Device ID
            'orderAsc'=>array(
                'order' => 'deviceID ASC',
            ),
            'orderDesc'=>array(
                'order' => 'deviceID DESC',
            ),
        );

    }

    /**
     * Parameterized Scope für App
     *
     * @param string $lAppId
     * @return $this
     */
    public function scopeApp($lAppId) {
        FW::gvTrace(func_get_args(),'FunctionCall',__METHOD__);

        $this->getDbCriteria()->mergeWith(array(
            'condition' => 't.appID=:appID',
            'params' => array(
                ':appID' => $lAppId,
            ),
        ));

        return $this;
    }

    /**
     * Parameterized Scope für Device Token
     *
     * @param string $sDeviceToken
     * @return $this
     */
    public function scopeDeviceToken($sDeviceToken) {
        FW::gvTrace(func_get_args(),'FunctionCall',__METHOD__);

        $this->getDbCriteria()->mergeWith(array(
            'condition' => 't.deviceToken=:deviceToken',
            'params' => array(
                ':deviceToken' => $sDeviceToken,
            ),
        ));

        return $this;
    }

    /**
     * Parameterized Named Scope Max Entries
     * Begrenzt die Anzahl der Elemente
     *
     * @param Integer $iMaxRetries
     * @return Device
     */
    public function scopeMaxEntries($iLimit) {
        $this->getDbCriteria()->mergeWith(array(
            'limit' => $iLimit,
        ));
        return $this;
    }

    /**
     * Parameterized Named Scope With Element
     * ermittelt alle Elemente der Devices, die NACH dem genannten Element kommen
     *
     * @param Integer $lDeviceID
     * @return Device
     */
    public function scopeAfterElement($lDeviceID) {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'deviceID > :deviceID',
            'params' => array(
                ':deviceID' => $lDeviceID,
            ),
            'order' => 'deviceID ASC',
        ));
        return $this;
    }

    /**
     * Parameterized Named Scope With Element
     * ermittelt alle Elemente der Devices, die NACH dem genannten Element kommen
     *
     * @param Integer $lDeviceID
     * @return Device
     */
    public function scopeServerStatus($lCountServer, $lServerID) {
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'MOD(deviceID, :countServer) = :serverID',
            'params' => array(
                ':countServer' => $lCountServer,
                ':serverID'    => $lServerID,
            ),
        ));
        return $this;
    }

    /**
     * Parameterized Named Scope With Group
     * ermittelt alle Elemente der Devices, die Teil der Gruppe (Group ID) sind
     *
     * @param Integer $lGroupID
     * @return Device
     */
    public function scopeWithGroup($lGroupID) {
        $this->getDbCriteria()->mergeWith(array(
            'join'      => 'LEFT JOIN {{device2Group}} d2g using(deviceID)',
            'condition' => 'd2g.groupID = :groupID',
            'params'    => array(
                ':groupID'    => $lGroupID,
            ),

        ));
        return $this;
    }

    /**
     * Speichert einen DeviceToken für eine App.<br/>
     * Ist das Token schon vorhanden, wird es aktualisiert. Im Fehlerfall wird ein Error-Array zurückgegeben
     *
     * @param String $sToken Token
     * @param Integer $lAppID ID der App
     * @return Array Ergebnismeldung der Speicherung
     */
    public static function gaRegisterToken($sToken, $lAppID) {
        FW::gvTrace(func_get_args(), 'FunctionCall', __METHOD__);

        try {
            $oDevice = self::moInsertOrUpdateDevice($sToken, $lAppID);

            // Ist ein Fehler beim Speichern aufgetreten?
            $aErrors = $oDevice->getErrors();
            if (empty($aErrors)) {
                return array('Success' => true);
            } else {
                $aReturn = ErrorCode::gaGetErrorData(FW_SAVE_FAILED);
                $aReturn['saveErrors'] = $aErrors;
                return $aReturn;
            }

        } catch(Exception $e) {
            FW::gvLog($e->getMessage(), 'ErrorLog', __METHOD__);
            return ErrorCode::gaGetErrorData(FW_ERROR_UNKNOWN);
        }
    }

    /**
     * Ermittelt die ID der zu den übergebenen Parametern gehörenden Devices.<br/>
     * Konnte kein Device in der Datenbank gefunden werden, so wird eines angelegt.
     *
     *
     * @param String $sToken Der Name innerhalb des Gruppentyps
     * @param Integer $lAppID Die ID der App zu der die Gruppe gehört
     * @return mixed Die ID der Gruppe oder null im Fehlerfall
     */
    public static function glGetDeviceID($sToken, $lAppID) {
        FW::gvTrace(func_get_args(), 'FunctionCall', __METHOD__);

        $lDeviceID = null;

        try {
            $oDevice = self::moInsertOrUpdateDevice($sToken, $lAppID);
            if (!is_null($oDevice)) {
                $lDeviceID = $oDevice->deviceID;
            }
        } catch(Exception $e) {
            FW::gvLog($e->getMessage(), 'ErrorLog', __METHOD__);
            return ErrorCode::gaGetErrorData(FW_ERROR_UNKNOWN);
        }

        return $lDeviceID;
    }

    /**
     * Speichert einen DeviceToken für eine App.<br/>
     * Ist das Token schon vorhanden, wird es aktualisiert. Im Fehlerfall wird null zurückgegeben
     *
     * @param String $sToken Token
     * @param Integer $lAppID ID der App
     * @return mixed gespeichertes Object oder null im Fehlerfall
     */
    private static function moInsertOrUpdateDevice($sToken, $lAppID) {
        FW::gvTrace(func_get_args(), 'FunctionCall', __METHOD__);

        $oDevice = null;

        try {
            $oTransaction = Yii::app()->db->beginTransaction();

            #$oDevice = new Device($lAppID);
            // Token für App schon vorhanden?
            #$oDevice = $oDevice->find('deviceToken = :token', array(':token' => $sToken));
            $oDevice = static::model()->scopeDeviceToken($sToken)->scopeApp($lAppID)->find();
            $bHasRace = false;
            if (is_null($oDevice)) {

                // Token neu anlegen
                $oDevice = new Device;
                $oDevice->appID       = $lAppID;
                $oDevice->deviceToken = $sToken;
                $oDevice->insertDate  = new CDbExpression('NOW()');
                $bHasRace = true; // Race Conditions nur bei neuen Tokens prüfen
            } else {
                $oDevice->setIsNewRecord(false);
            }
            $oDevice->updateDate = new CDbExpression('NOW()');

            // Speichern
            $oDevice->save();

            // Race Conditions? n parallele Prozesse für das gleiche Token
            // nach dem Insert ein Select durchgeführt werden,
            // in dem der Eintrag zu dem Token mit der niedrigsten deviceID ausgelesen wird.
            // Stimmt die gelesene deviceID nicht mit der eigenen überein, muss die eigene eine Doublette sein und entfernt werden
            if ($bHasRace) {
                $oDeviceRace = static::model()->orderAsc()->scopeDeviceToken($sToken)->scopeApp($lAppID)->find();
                if ($oDevice->deviceID != $oDeviceRace->deviceID) {
                    // IDs stimmen nicht überein
                    $oDevice->delete();
                    $oDevice = $oDeviceRace;
                }
            }
            $oTransaction->commit();


        } catch(Exception $e) {
            FW::gvLog($e->getMessage(), 'ErrorLog', __METHOD__);
            $oTransaction->rollback();
        }

        return $oDevice;
    }

    /**
     * Löscht einen DeviceToken für eine App.<br/>
     * Ist das Token nicht vorhanden, führt dies zu einer Erfolgsmeldung. Eventuelle
     * Gruppenzuordnungen werden ebenfalls gelöscht.
     *
     * @param String $sToken Token
     * @param Integer $lAppID ID der App
     * @return Array Ergebnismeldung der Löschung
     */
    public static function gaDeregisterToken($sToken, $lAppID) {
        FW::gvTrace(func_get_args(), 'FunctionCall', __METHOD__);

        try {
            // Token für App schon vorhanden?
            #$oDevice = new Device($lAppID);
            #$oDevice = $oDevice->find('deviceToken = :token', array(':token' => $sToken));

            $oDevice = static::model()->scopeDeviceToken($sToken)->scopeApp($lAppID)->find();

            if (!is_null($oDevice)) {
                $oDevice->setIsNewRecord(false);
                // Löschen
                if ($oDevice->delete()) {
                    return array('Success' => true);
                } else {
                    $aReturn = ErrorCode::gaGetErrorData(FW_TOKEN_DELETE_FAILED);
                    return $aReturn;
                }
            } else {
                return array('Success' => true);
            }
        } catch(Exception $e) {
            FW::gvLog($e->getMessage(), 'ErrorLog', __METHOD__);
            return ErrorCode::gaGetErrorData(FW_ERROR_UNKNOWN);
        }
    }

    /**
     * Aktualisiert einen Token in der Tabelle.
     *
     * @param string $sOldToken Token der App
     * @param string $sNewToken Token der App
     * @param integer $lAppID ID der App
     *
     * @return null
     */
    public function gvUpdateToken($sOldToken, $sNewToken, $lAppID)
    {
        FW::gvTrace(func_get_args(),'FunctionCall',__METHOD__);
        try
        {
            #$oDevice = new Device($lAppID);
            #$oDevice = $oDevice->find('deviceToken = :token', array(':token' => $sOldToken));
            $oDevice = static::model()->scopeDeviceToken($sOldToken)->scopeApp($lAppID)->find();

            if (!is_null($oDevice)) {
                #$oNewDevice = $oDevice->find('deviceToken = :token', array(':token' => $sNewToken));
                $oNewDevice = static::model()->scopeDeviceToken($sNewToken)->scopeApp($lAppID)->find();
                FW::gvTrace($oNewDevice, 'VarDump', 'trace', __METHOD__);
                if (is_null($oNewDevice)) {
                    $oDevice->deviceToken = $sNewToken;
                    $oDevice->updateDate = new CDbExpression('NOW()'); // date('Y-m-d H:i:s');
                    $oDevice->isNewRecord = false;
                    $oDevice->save();

                    if ($oDevice->hasErrors()) {
                        FW::gvTrace($oDevice->getErrors(),'error',__METHOD__);
                    }
                } else {
                    $oDevice->delete();
                }
            }
        }
        catch(Exception $e)
        {
            FW::gvLog($e->getMessage(), 'error', __METHOD__, "error");
        }
    }
}
