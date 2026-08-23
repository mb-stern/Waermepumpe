class HeatPumpCard extends HTMLElement {

  // Whenever the state changes, a new `hass` object is set. Use this to
  // update your content.
  set hass(hass) {

    if (this.content) {
      this.setValues(hass);
    } else {
      // Load resources and Initialize the content if it's not there yet.
      this.readSvg(hass.language.substring(0,2), this.handleSvg, hass);
    }
  }

  setValues(hass) {
    this.changeHeatPumpRunning(this.content, this.config.heatingPumpType, this.config.hpRunning, hass);
    this.content.querySelector("#textG2WWaterTempIn").innerHTML = this.formatNum(hass, this.config.temperatureGroundWaterIn);
    this.content.querySelector("#textG2WWaterTempOut").innerHTML = this.formatNum(hass, this.config.temperatureGroundWaterOut);

    this.content.querySelector("#gHPStatusOff").style.display = this.formatBinary(hass, this.config.heatingPumpStatusOnOff) ? "none" : "inline";
    this.content.querySelector("#gHPStatusWW").style.display = this.formatBinary(hass, this.config.heatingPumpHotWaterMode) ? "inline" : "none";
    this.content.querySelector("#gHPStatusHeating").style.display = this.formatBinary(hass, this.config.heatingPumpHeatingMode) ? "inline" : "none";
    this.content.querySelector("#gHPStatusCooling").style.display = this.formatBinary(hass, this.config.heatingPumpCoolingMode) ? "inline" : "none";

    const heatingPumpPartyMode = this.formatBinary(hass, this.config.heatingPumpPartyMode);
    this.content.querySelector("#gHPStatusParty").style.display = heatingPumpPartyMode ? "inline" : "none";

    this.content.querySelector("#gHPStatusSave").style.display = this.formatBinary(hass, this.config.heatingPumpEnergySaveMode) ? "inline" : "none";

    const heatingPumpNightMode = this.formatBinary(hass, this.config.heatingPumpNightMode);
    this.content.querySelector("#gTimeSymbolNight").style.display = heatingPumpNightMode ? "inline" : "none";
    this.content.querySelector("#gTimeSymbolDay").style.display = heatingPumpNightMode ? "none" : "inline";

    this.content.querySelector("#gWarning").style.display = this.formatBinary(hass, this.config.warning) ? "inline" : "none";
    this.content.querySelector("#gError").style.display = (this.formatBinary(hass, this.config.error) || this.formatBinary(hass, this.config.warning)) ? "inline" : "none";
    this.content.querySelector("#gDefrost").style.display = this.formatBinary(hass, this.config.defrostMode) ? "inline" : "none";
    this.content.querySelector("#gAdditionalHeating").style.display = this.formatBinary(hass, this.config.additionalHeating) ? "inline" : "none";

    this.content.querySelector("#textOutdoorTemperatureValue").innerHTML = this.formatNum(hass, this.config.outdoorTemperature);

    const ambientTemperatureReduced = this.formatNum(hass, this.config.ambientTemperatureReduced);
    const ambientTemperatureParty = this.formatNum(hass, this.config.ambientTemperatureParty);
    if (heatingPumpPartyMode && ambientTemperatureParty) {
      this.content.querySelector("#textIndoorTemperatureValue").innerHTML = ambientTemperatureParty;
    } else if (heatingPumpNightMode && ambientTemperatureReduced) {
      this.content.querySelector("#textIndoorTemperatureValue").innerHTML = ambientTemperatureReduced;
    } else {
      this.content.querySelector("#textIndoorTemperatureValue").innerHTML = this.formatNum(hass, this.config.ambientTemperatureNormal);
    }

    this.content.querySelector("#textSupplyTemperatureValue").innerHTML = this.formatNum(hass, this.config.supplyTemperature);

    this.switchRotateAttribute("#pathCompressor", hass, this.config.compressorRunning);
    if (this.config.heatingCircuitPumpRunning) {
      this.switchRotateAttribute("#gHeatingCircuitPump", hass, this.config.heatingCircuitPumpRunning);
    }
    if (this.config.heatingCircuitPumpRunning2) {
      this.switchRotateAttribute("#gHeatingCircuitPump2", hass, this.config.heatingCircuitPumpRunning2);
    }
    if (this.config.heatingCircuitPumpRunning3) {
      this.switchRotateAttribute("#gHeatingCircuitPump3", hass, this.config.heatingCircuitPumpRunning3);
    }
    if (this.config.circulatingPumpRunning) {
      this.switchRotateAttribute("#gCirculatingPumpBladeWheel", hass, this.config.circulatingPumpRunning);
    }
    if (this.config.storageChargingPumpRunning) {
      this.switchRotateAttribute("#gStorageChargingPump", hass, this.config.storageChargingPumpRunning);
    }

    const heaterRodWW = this.formatBinary(hass, this.config.heaterRodWW);

    if (this.config.tankHP) {
      const tankTempHPUp = this.readState(hass, this.config.tankTempHPUp);
      this.content.querySelector("#textTankTempHPUp").innerHTML = this.formatNumValue(tankTempHPUp);
      const tankTempHPMiddle = this.readState(hass, this.config.tankTempHPMiddle);
      this.content.querySelector("#textTankTempHPMiddle").innerHTML = this.formatNumValue(tankTempHPMiddle);
      const tankTempHPDown = this.readState(hass, this.config.tankTempHPDown);
      this.content.querySelector("#textTankTempHPDown").innerHTML = this.formatNumValue(tankTempHPDown);
      this.tankColors(this.content, tankTempHPUp, tankTempHPMiddle, tankTempHPDown, "#stop3020", "#stop3040", "#stop3030");

      this.content.querySelector("#pathHeaterRodHP").style.display =  this.formatBinary(hass, this.config.heaterRodHP) ? 'block' : 'none';
    }

    if (this.config.tankWW) {
      const tankTempWWUp = this.readState(hass, this.config.tankTempWWUp);
      this.content.querySelector("#textTankTempWWUp").innerHTML = this.formatNumValue(tankTempWWUp);
      const tankTempWWMiddle = this.readState(hass, this.config.tankTempWWMiddle);
      this.content.querySelector("#textTankTempWWMiddle").innerHTML = this.formatNumValue(tankTempWWMiddle);
      const tankTempWWDown = this.readState(hass, this.config.tankTempWWDown);
      this.content.querySelector("#textTankTempWWDown").innerHTML = this.formatNumValue(tankTempWWDown);
      this.tankColors(this.content, tankTempWWUp, tankTempWWMiddle, tankTempWWDown, "#stop3050", "#stop3070", "#stop3060");

      this.content.querySelector("#gWWHeatingValve").setAttribute('transform', 'rotate(' + (this.formatBinary(hass, this.config.wwHeatingValve) ? '90' : '0') + ', 620, 450)');
      this.content.querySelector("#pathHeaterRodWW").style.display = heaterRodWW ? 'block' : 'none';
    }

    this.heatingCurcuit1(this.content, hass);
    this.heatingCurcuit2(this.content, hass);
    this.heatingCurcuit3(this.content, hass);

    this.content.querySelector("#textEvaporatorPressure").innerHTML = this.formatNum(hass, this.config.evaporatorPressure);
    this.content.querySelector("#textEvaporatorTemperature").innerHTML = this.formatNum(hass, this.config.evaporatorTemperature);
    this.content.querySelector("#textCondenserPressure").innerHTML = this.formatNum(hass, this.config.condenserPressure);
    this.content.querySelector("#textCondenserTemperature").innerHTML = this.formatNum(hass, this.config.condenserTemperature);
    this.content.querySelector("#textExpansionValveOpening").innerHTML = this.formatNum(hass, this.config.expansionValveOpening);
    this.content.querySelector("#textCompressorValue").innerHTML = this.readStateValue(hass, this.config.compressorValue);

    this.heaterRodColor(this.content, heaterRodWW, this.formatBinary(hass, this.config.heaterRodLevel1), this.formatBinary(hass, this.config.heaterRodLevel2));

    if (this.config.thermalSolarAvailable) {
      this.switchRotateAttribute("#gThermalSolarPump", hass, this.config.thermalSolarPump);
      this.content.querySelector("#textThermalSolarPanelTemp").innerHTML = this.formatNum(hass, this.config.thermalSolarPanelTemp);
      this.content.querySelector("#textThermalSolarFluxTemp").innerHTML = this.formatNum(hass, this.config.thermalSolarFluxTemp);
      this.content.querySelector("#textThermalSolarPumpSpeed").innerHTML = this.formatNum(hass, this.config.thermalSolarPumpSpeed);
    }

    this.content.querySelector("#textValue000").innerHTML = this.readStateValue(hass, this.config.additionalValue000);
    this.content.querySelector("#textValue001").innerHTML = this.readStateValue(hass, this.config.additionalValue001);
    this.content.querySelector("#textValue002").innerHTML = this.readStateValue(hass, this.config.additionalValue002);
    this.content.querySelector("#textValue003").innerHTML = this.readStateValue(hass, this.config.additionalValue003);
    this.content.querySelector("#textValue004").innerHTML = this.readStateValue(hass, this.config.additionalValue004);
    this.content.querySelector("#textValue005").innerHTML = this.readStateValue(hass, this.config.additionalValue005);
    this.content.querySelector("#textValue006").innerHTML = this.readStateValue(hass, this.config.additionalValue006);
    this.content.querySelector("#textValue007").innerHTML = this.readStateValue(hass, this.config.additionalValue007);
    this.content.querySelector("#textValue008").innerHTML = this.readStateValue(hass, this.config.additionalValue008);
    this.content.querySelector("#textValue009").innerHTML = this.readStateValue(hass, this.config.additionalValue009);
    this.content.querySelector("#textValue010").innerHTML = this.readStateValue(hass, this.config.additionalValue010);
    this.content.querySelector("#textValue011").innerHTML = this.readStateValue(hass, this.config.additionalValue011);
    this.content.querySelector("#textValue012").innerHTML = this.readStateValue(hass, this.config.additionalValue012);
  }

  static cardFolder = "/hacsfiles/lovelace-heat-pump-card/heat-pump-card/";

  readLocalization(lang, hass) {
    const translationLocal = HeatPumpCard.cardFolder + lang + ".json?" + new Date().getTime();
    var rawFile = new XMLHttpRequest();
    rawFile.overrideMimeType("application/json");
    rawFile.open("GET", translationLocal, true);
    rawFile.onload = (e) => {
      if (rawFile.readyState === 4) {
        if (rawFile.status == 200) {
          HeatPumpCard.localization = JSON.parse(rawFile.responseText);
          this.content.querySelector("#textTankWWName").innerHTML = HeatPumpCard.localization.svgTexts['tankWWName'];
          this.content.querySelector("#textTankHPName").innerHTML = HeatPumpCard.localization.svgTexts['tankHPName'];
          this.content.querySelector("#textEvaporator").innerHTML = HeatPumpCard.localization.svgTexts['evaporator'];
          this.content.querySelector("#textCondenser").innerHTML = HeatPumpCard.localization.svgTexts['condenser'];
          this.content.querySelector("#textCompressor").innerHTML = HeatPumpCard.localization.svgTexts['compressor'];
          this.content.querySelector("#textExpansionValve").innerHTML = HeatPumpCard.localization.svgTexts['expansionValve'];
          this.content.querySelector("#textCirculatingPump").innerHTML = HeatPumpCard.localization.svgTexts['circulatingPump'];
          this.content.querySelector("#textSupplyTemperatureLabel").innerHTML = HeatPumpCard.localization.svgTexts['supplyTemperatureLabel'];
          this.setConfig(this.config);
          this.setValues(hass);
        } else if (lang != "en") {
          this.readLocalization("en", hass);
        } else {
          console.error("No localization for " + lang);
        }
      }
    };
    rawFile.onerror = (e) => {
      console.error(rawFile.statusText);
    };
    rawFile.send(null);
  }

  readSvg(lang, handleSvg, hass) {
    const svgImage = HeatPumpCard.cardFolder + "heat-pump.svg?" + new Date().getTime();
    var rawFile = new XMLHttpRequest();
    rawFile.open("GET", svgImage, true);
    rawFile.onload = (e) => {
      if (rawFile.readyState === 4) {
        if (rawFile.status == 200) {
          this.innerHTML = '<ha-card>\n' + rawFile.responseText.replace(/.*--primary-text-color:.*/g, "").replace(/ class="rotate"/g, "").replace(/display: inline;/g, "display: none;") + '</ha-card>';
          this.content = this.querySelector("svg");
          this.content.querySelector("#linkDetails").addEventListener("click", this.linkHandling);
          this.content.querySelector("#linkSettings").addEventListener("click", this.linkHandling);
          this.readLocalization(lang, hass);
        } else {
          alert("Can't read svg image " + rawFile.statusText);
        }
      }
    };
    rawFile.onerror = (e) => {
      console.error(rawFile.statusText);
    };
    rawFile.send(null);
    if (rawFile.status == 200) {
      return rawFile.responseText;
    }
    return null;
  }

  setLinks() {
    if (this.content && this.config) {
      if (this.config.linkDetails) {
        this.content.querySelector("#linkDetails").setAttribute('href', this.config.linkDetails);
      }
      if (this.config.linkSettings) {
        this.content.querySelector("#linkSettings").setAttribute('href', this.config.linkSettings);
      }
    }
  }

  switchRotateAttribute(attributeName, hass, state) {
    if (this.formatBinary(hass, state)) {
      this.content.querySelector(attributeName).classList.add("rotate");
    } else {
      this.content.querySelector(attributeName).classList.remove("rotate");
    }
  }

  readState(hass, config) {
    if (config) {
      return hass.states[config];
    }
    return null;
  }

  readStateValue(hass, config) {
    const stateValue = this.readState(hass, config);
    const valueNumeric = this.formatNumValue(stateValue);
    return valueNumeric ? valueNumeric : (stateValue ? stateValue.state : '');
  }

  formatBinary(hass, state) {
    const stateValue = this.readState(hass, state);
    return stateValue && stateValue.state  === "on";
  }

  formatNum(hass, state) {
    const stateValue = this.readState(hass, state);
    return this.formatNumValue(stateValue);
  }

  formatNumValue(stateValue) {
    if (stateValue && !isNaN(Number(stateValue.state))) {
      const unit = stateValue.attributes.unit_of_measurement ? stateValue.attributes.unit_of_measurement :  "";
      return new Intl.NumberFormat(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1}).format(stateValue.state) + " " + unit;
    }
    return null;
  }

  heaterRodColor(content, heaterRodWW, heaterRodLevel1, heaterRodLevel2) {
    var colorHS = "#ffffff";
    if (heaterRodLevel1) {
      colorHS = this.tempColor(40);
    } else if (heaterRodLevel2) {
      colorHS = this.tempColor(60);
    }
    if (heaterRodWW) {
      content.querySelector("#pathHeaterRodWW").style.stroke = colorHS;
      content.querySelector("#pathHeaterRodHP").style.stroke = "#ffffff";
    } else {
      content.querySelector("#pathHeaterRodHP").style.stroke = colorHS;
      content.querySelector("#pathHeaterRodWW").style.stroke = "#ffffff";
    }
  }

  tempColor(temp) {
    if (!temp) {
      return "#ffffff00";
    }
    if (temp > 60) {
      return "#ff0000"
    }
    return "#" + ("0" + Math.round(255 * temp / 60).toString(16)).substr(-2) + "00" + ("0" + Math.round(255 * Math.abs(60 - temp) / 60).toString(16)).substr(-2);
  }

  tankColors(content, tankTempUp, tankTempMiddle, tankTempDown, idUp, idMiddle, idDown) {
    var tempUp = tankTempUp && !isNaN(Number(tankTempUp.state)) ? tankTempUp.state : null;
    var tempMiddle = tankTempMiddle && !isNaN(Number(tankTempMiddle.state)) ? tankTempMiddle.state : null;
    var tempDown = tankTempDown && !isNaN(Number(tankTempDown.state)) ? tankTempDown.state : null;
    if (tempUp) {
      if (!tempMiddle) {
        if (tempDown) {
          tempMiddle = (tempUp + tempDown) / 2;
        } else {
          tempMiddle = tempUp - 5;
        }
      }
    } else {
      if (tempMiddle) {
        tempUp = tempMiddle + 5;
      } else if (tempDown) {
        tempMiddle = tempDown + 5;
        tempUp = tempDown + 10;
      }
    }
    if (tempMiddle && !tempDown) {
      tempDown = tempMiddle - 5;
    }
    content.querySelector(idUp).setAttribute('style', "stop-color:" + this.tempColor(tempUp));
    content.querySelector(idMiddle).setAttribute('style', "stop-color:" + this.tempColor(tempMiddle));
    content.querySelector(idDown).setAttribute('style', "stop-color:" + this.tempColor(tempDown));
  }

  linkHandling(event) {
    event.preventDefault();
    window.history.pushState(null,"",this.getAttribute('href'));
    window.dispatchEvent(new CustomEvent("location-changed"));
  }

  changeHeatPumpRunning(content, selection, running, hass) {
    this.switchRotateAttribute("#pathHPFan", hass, !selection || selection === 'A2W' ? running : null);
    this.switchRotateAttribute("#gHPW2WPumpBladeWheel", hass, selection === 'W2W' ? running : null);
    this.switchRotateAttribute("#gHPG2WPumpBladeWheel", hass, selection === 'G2W' ? running : null);
  }

  heatingCurcuit1(content, hass) {
    var type = this.config.heatingCircuitType1;
    if (type && type != 'off') {
      var tempInState = this.readState(hass, this.config.supplyTemperatureHeating);
      var tempIn = tempInState ? tempInState.state : 30;
      var tempOutState = this.readState(hass, this.config.refluxTemperatureHeating);
      var tempOut = Math.max(0, tempOutState ? tempOutState.state : tempIn - 10);
      content.querySelector('#stopCircuit1').setAttribute('style', "stop-color:" + this.tempColor(tempIn));
      content.querySelector('#stopCircuit2').setAttribute('style', "stop-color:" + this.tempColor(tempOut));
      content.querySelector("#textSupplyTemperatureHeating").innerHTML = this.formatNumValue(tempInState);
      content.querySelector("#textRefluxTemperatureHeating").innerHTML = this.formatNumValue(tempOutState);
    }
  }

  heatingCurcuit2(content, hass) {
    var type = this.config.heatingCircuitType2;
    if (type && type != 'off') {
      var tempInState = this.readState(hass, this.config.supplyTemperatureHeating2);
      var tempIn = tempInState ? tempInState.state : 30;
      var tempOutState = this.readState(hass, this.config.refluxTemperatureHeating2);
      var tempOut = Math.max(0, tempOutState ? tempOutState.state : tempIn - 10);
      content.querySelector('#stopCircuit3').setAttribute('style', "stop-color:" + this.tempColor(tempIn));
      content.querySelector('#stopCircuit4').setAttribute('style', "stop-color:" + this.tempColor(tempOut));
      content.querySelector("#textSupplyTemperatureHeating2").innerHTML = this.formatNumValue(tempInState);
      content.querySelector("#textRefluxTemperatureHeating2").innerHTML = this.formatNumValue(tempOutState);
    }
  }

  heatingCurcuit3(content, hass) {
    var type = this.config.heatingCircuitType3;
    if (type && type != 'off') {
      var tempInState = this.readState(hass, this.config.supplyTemperatureHeating3);
      var tempIn = tempInState ? tempInState.state : 30;
      var tempOutState = this.readState(hass, this.config.refluxTemperatureHeating3);
      var tempOut = Math.max(0, tempOutState ? tempOutState.state : tempIn - 10);
      content.querySelector('#stopCircuit5').setAttribute('style', "stop-color:" + this.tempColor(tempIn));
      content.querySelector('#stopCircuit6').setAttribute('style', "stop-color:" + this.tempColor(tempOut));
      content.querySelector("#textSupplyTemperatureHeating3").innerHTML = this.formatNumValue(tempInState);
      content.querySelector("#textRefluxTemperatureHeating3").innerHTML = this.formatNumValue(tempOutState);
    }
  }

  // The user supplied configuration. Throw an exception and Home Assistant
  // will render an error card.
  setConfig(config) {
    this.config = config;
    if (this.content) {
      this.querySelector("ha-card").setAttribute("header", config.title);
      this.content.querySelector('#gHPFan').style.display = (!config.heatingPumpType || config.heatingPumpType === 'A2W' ? 'inline' : 'none');
      this.content.querySelector('#gHPW2W').style.display = (config.heatingPumpType === 'W2W' ? 'inline' : 'none');
      this.content.querySelector('#gHPG2W').style.display = (config.heatingPumpType === 'G2W' ? 'inline' : 'none');
      this.content.querySelector("#gCirculatingPump").style.display = config.circulatingPumpRunning ? 'inline' : 'none';
      this.content.querySelector('#gCirculatingPumpBladeWheel').classList.remove("rotate");
      this.content.querySelector("#gStorageChargingPump").style.display = config.storageChargingPumpRunning ? 'inline' : 'none';
      this.content.querySelector('#gStorageChargingPump').classList.remove("rotate");
      this.content.querySelector("#gTankHP").style.display = config.tankHP ? 'inline' : 'none';
      this.content.querySelector("#gWW").style.display = config.tankWW ? 'inline' : 'none';

      var type1 = config.heatingCircuitType1;
      if (!type1 || type1 === 'off') {
        this.content.querySelector('#gHeaterCircuit1').style.display = 'none';
      } else {
        this.content.querySelector('#gHeaterCircuit1').style.display = 'inline';
        this.content.querySelector('#gHeaterCircuitFloor1').style.display = (type1 === 'underfloor' ? 'inline' : 'none');
        this.content.querySelector('#radiator1').style.display = (type1 === 'radiator' ? 'inline' : 'none');
      }
      this.content.querySelector('#gHeatingCircuitPump').classList.remove("rotate");
      this.content.querySelector("#gHeatingCircuitPump").style.display = config.heatingCircuitPumpRunning ? 'inline' : 'none';

      var type2 = config.heatingCircuitType2;
      if (!type2 || type2 === 'off') {
        this.content.querySelector('#gHeaterCircuit2').style.display = 'none';
      } else {
        this.content.querySelector('#gHeaterCircuit2').style.display = 'inline';
        this.content.querySelector('#gHeaterCircuitFloor2').style.display = (type2 === 'underfloor' ? 'inline' : 'none');
        this.content.querySelector('#radiator2').style.display = (type2 === 'radiator' ? 'inline' : 'none');
      }
      this.content.querySelector('#gHeatingCircuitPump2').classList.remove("rotate");
      this.content.querySelector("#gHeatingCircuitPump2").style.display = config.heatingCircuitPumpRunning2 ? 'inline' : 'none';
      this.content.querySelector("#pathPipeToHP2").style.display = (!type1 || type1 === 'off') && (!type2 || type2 === 'off') ? 'none' : 'inline';

      var type3 = config.heatingCircuitType3;
      if (!type3 || type3 === 'off') {
        this.content.querySelector('#gHeaterCircuit3').style.display = 'none';
      } else {
        this.content.querySelector('#gHeaterCircuit3').style.display = 'inline';
        this.content.querySelector('#gHeaterCircuitFloor3').style.display = (type3 === 'underfloor' ? 'inline' : 'none');
        this.content.querySelector('#radiator3').style.display = (type3 === 'radiator' ? 'inline' : 'none');
      }
      this.content.querySelector('#gHeatingCircuitPump3').classList.remove("rotate");
      this.content.querySelector("#gHeatingCircuitPump3").style.display = config.heatingCircuitPumpRunning3 ? 'inline' : 'none';

      var noHeating = (!type1 || type1 === 'off') && (!type2 || type2 === 'off') && (!type3 || type3 === 'off') && !config.tankHP;
      var noHotWater = !config.tankWW;

      if (noHeating && noHotWater) {
        this.content.querySelector("#gPipe").style.display = 'none';
        this.content.querySelector("#gPipeBuffer").style.display = 'none';
        this.content.querySelector("#gPipeLayeredChargeStorage").style.display = 'none';
        this.content.querySelector("#gHP").setAttribute("transform", "translate(460 -300)");
        this.content.querySelector("#gSettings").setAttribute("transform", "translate(-25)");
      } else {
        this.content.querySelector("#pathPipeToBuffer").style.display = noHeating ? 'none' : 'inline';
        this.content.querySelector("#pathPipeFromBuffer").style.display = noHeating ? 'none' : 'inline';
        this.content.querySelector("#gPipe").style.display = 'inline';
        this.content.querySelector("#gPipeBuffer").style.display = config.layeredChargeStorage ? 'none' : 'inline';
        this.content.querySelector("#gPipeLayeredChargeStorage").style.display = config.layeredChargeStorage ? 'inline' : 'none';
        this.content.querySelector("#gWWHeatingValve").style.display = config.layeredChargeStorage ? 'none' : 'inline';
        this.content.querySelector("#gHP").removeAttribute("transform");
        this.content.querySelector("#gSettings").removeAttribute("transform");
      }

      if (!config.thermalSolarAvailable || config.thermalSolarAvailable === 'off') {
        this.content.querySelector('#gThermalSolar').style.display = 'none';
      } else {
        this.content.querySelector('#gThermalSolar').style.display = 'inline';
      }
      this.content.querySelector('#gThermalSolarPump').classList.remove("rotate");

      this.content.querySelector("#textLabel000").innerHTML = config.additionalLabel000 ? config.additionalLabel000 : '';
      this.content.querySelector("#textLabel001").innerHTML = config.additionalLabel001 ? config.additionalLabel001 : '';
      this.content.querySelector("#textLabel002").innerHTML = config.additionalLabel002 ? config.additionalLabel002 : '';
      this.content.querySelector("#textLabel003").innerHTML = config.additionalLabel003 ? config.additionalLabel003 : '';
      this.content.querySelector("#textLabel004").innerHTML = config.additionalLabel004 ? config.additionalLabel004 : '';
      this.content.querySelector("#textLabel005").innerHTML = config.additionalLabel005 ? config.additionalLabel005 : '';
      this.content.querySelector("#textLabel006").innerHTML = config.additionalLabel006 ? config.additionalLabel006 : '';
      this.content.querySelector("#textLabel007").innerHTML = config.additionalLabel007 ? config.additionalLabel007 : '';
      this.content.querySelector("#textLabel008").innerHTML = config.additionalLabel008 ? config.additionalLabel008 : '';
      this.content.querySelector("#textLabel009").innerHTML = config.additionalLabel009 ? config.additionalLabel009 : '';
      this.content.querySelector("#textLabel010").innerHTML = config.additionalLabel010 ? config.additionalLabel010 : '';
      this.content.querySelector("#textLabel011").innerHTML = config.additionalLabel011 ? config.additionalLabel011 : '';
      this.content.querySelector("#textLabel012").innerHTML = config.additionalLabel012 ? config.additionalLabel012 : '';

      this.setLinks();
    }
  }

  static getConfigForm() {
    // Define the form schema.
    const SCHEMA = [
      { name: "title", selector: { text: {} } },
      {
        name: "heatingPumpType",
        default: "A2W",
        selector: {
          select: {
            options: [
              { value: "A2W", label: "Air to Water" },
              { value: "W2W", label: "Water to Water"},
              { value: "G2W", label: "Ground to Water"},
            ],
          },
        },
      },
      { type: "expandable",
        name: "groundWaterInOut",
        flatten: true,
        schema: [
          { name: "temperatureGroundWaterIn", selector: { entity: {domain: ["sensor"]} } },
          { name: "temperatureGroundWaterOut", selector: { entity: {domain: ["sensor"]} } },
        ],
      },
      { type: "expandable",
        name: "states",
        flatten: true,
        schema: [
          { name: "heatingPumpStatusOnOff", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "heatingPumpHotWaterMode", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "heatingPumpHeatingMode", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "heatingPumpCoolingMode", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "heatingPumpPartyMode", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "heatingPumpEnergySaveMode", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "heatingPumpNightMode", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "warning", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "error", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "defrostMode", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "additionalHeating", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "hpRunning", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "compressorRunning", selector: { entity: {domain: ["binary_sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "temperatures",
        flatten: true,
        schema: [
          { name: "outdoorTemperature", selector: { entity: {domain: ["sensor"]} } },
          { name: "ambientTemperatureNormal", selector: { entity: {domain: ["sensor", "number"]} } },
          { name: "ambientTemperatureReduced", selector: { entity: {domain: ["sensor", "number"]} } },
          { name: "ambientTemperatureParty", selector: { entity: {domain: ["sensor", "number"]} } },
          { name: "supplyTemperature", selector: { entity: {domain: ["sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "bufferTank",
        flatten: true,
        schema: [
          { name: "tankHP", default: true, selector: { boolean: {} } },
          { name: "tankTempHPUp", selector: { entity: {domain: ["sensor"]} } },
          { name: "tankTempHPMiddle", selector: { entity: {domain: ["sensor"]} } },
          { name: "tankTempHPDown", selector: { entity: {domain: ["sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "hotWaterTank",
        flatten: true,
        schema: [
          { name: "tankWW", default: true, selector: { boolean: {} } },
          { name: "layeredChargeStorage", default: false, selector: { boolean: {} } },
          { name: "tankTempWWUp", selector: { entity: {domain: ["sensor"]} } },
          { name: "tankTempWWMiddle", selector: { entity: {domain: ["sensor"]} } },
          { name: "tankTempWWDown", selector: { entity: {domain: ["sensor"]} } },
          { name: "wwHeatingValve", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "circulatingPumpRunning", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "storageChargingPumpRunning", selector: { entity: {domain: ["binary_sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "heatingCircuit1",
        flatten: true,
        schema: [
          {
            name: "heatingCircuitType1",
            default: "off",
            selector: {
              select: {
                options: [
                  { value: "off", label: "Off" },
                  { value: "underfloor", label: "Underfloor"},
                  { value: "radiator", label: "Radiator"},
                ],
              },
            },
          },
          { name: "heatingCircuitPumpRunning", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "supplyTemperatureHeating", selector: { entity: {domain: ["sensor"]} } },
          { name: "refluxTemperatureHeating", selector: { entity: {domain: ["sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "heatingCircuit2",
        flatten: true,
        schema: [
          {
            name: "heatingCircuitType2",
            default: "off",
            selector: {
              select: {
                options: [
                  { value: "off", label: "Off" },
                  { value: "underfloor", label: "Underfloor"},
                  { value: "radiator", label: "Radiator"},
                ],
              },
            },
          },
          { name: "heatingCircuitPumpRunning2", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "supplyTemperatureHeating2", selector: { entity: {domain: ["sensor"]} } },
          { name: "refluxTemperatureHeating2", selector: { entity: {domain: ["sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "heatingCircuit3",
        flatten: true,
        schema: [
          {
            name: "heatingCircuitType3",
            default: "off",
            selector: {
              select: {
                options: [
                  { value: "off", label: "Off" },
                  { value: "underfloor", label: "Underfloor"},
                  { value: "radiator", label: "Radiator"},
                ],
              },
            },
          },
          { name: "heatingCircuitPumpRunning3", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "supplyTemperatureHeating3", selector: { entity: {domain: ["sensor"]} } },
          { name: "refluxTemperatureHeating3", selector: { entity: {domain: ["sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "heatPumpSensors",
        flatten: true,
        schema: [
          { name: "evaporatorPressure", selector: { entity: {domain: ["sensor"]} } },
          { name: "evaporatorTemperature", selector: { entity: {domain: ["sensor"]} } },
          { name: "condenserPressure", selector: { entity: {domain: ["sensor"]} } },
          { name: "condenserTemperature", selector: { entity: {domain: ["sensor"]} } },
          { name: "expansionValveOpening", selector: { entity: {domain: ["sensor"]} } },
          { name: "compressorValue", selector: { entity: {domain: ["sensor"]} } }
        ],
      },
      { type: "expandable",
        name: "heaterRod",
        flatten: true,
        schema: [
          { name: "heaterRodWW", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "heaterRodHP", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "heaterRodLevel1", selector: { entity: {domain: ["binary_sensor", "switch"]} } },
          { name: "heaterRodLevel2", selector: { entity: {domain: ["binary_sensor", "switch"]} } }
        ],
      },
      { type: "expandable",
        name: "thermalSolar",
        flatten: true,
        schema: [
          { name: "thermalSolarAvailable", default: false, selector: { boolean: {} } },
          { name: "thermalSolarPanelTemp", selector: { entity: {domain: ["sensor", "number"]} } },
          { name: "thermalSolarFluxTemp", selector: { entity: {domain: ["sensor", "number"]} } },
          { name: "thermalSolarPump", selector: { entity: {domain: ["binary_sensor"]} } },
          { name: "thermalSolarPumpSpeed", selector: { entity: {domain: ["sensor", "number"]} } }
        ],
      },
      { type: "expandable",
        name: "links",
        flatten: true,
        schema: [
          { name: "linkDetails", selector: { navigation: {} } },
          { name: "linkSettings", selector: { navigation: {} } }
        ],
      },
      { type: "expandable",
        name: "additionalValues",
        flatten: true,
        schema: [
          { name: "additionalLabel000", selector: { text: {} } },
          { name: "additionalValue000", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel001", selector: { text: {} } },
          { name: "additionalValue001", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel002", selector: { text: {} } },
          { name: "additionalValue002", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel003", selector: { text: {} } },
          { name: "additionalValue003", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel004", selector: { text: {} } },
          { name: "additionalValue004", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel005", selector: { text: {} } },
          { name: "additionalValue005", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel006", selector: { text: {} } },
          { name: "additionalValue006", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel007", selector: { text: {} } },
          { name: "additionalValue007", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel008", selector: { text: {} } },
          { name: "additionalValue008", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel009", selector: { text: {} } },
          { name: "additionalValue009", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel010", selector: { text: {} } },
          { name: "additionalValue010", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel011", selector: { text: {} } },
          { name: "additionalValue011", selector: { entity: {domain: ["sensor"]} } },
          { name: "additionalLabel012", selector: { text: {} } },
          { name: "additionalValue012", selector: { entity: {domain: ["sensor"]} } }
        ],
      }
    ];

    // A simple assertion function to validate the configuration.
    const assertConfig = (config) => {
    };

    // computeLabel returns a localized label for a schema item.
    const computeLabel = (schema, localize) => {
      return HeatPumpCard.localization.editor[schema.name];
    };

    return {
      schema: SCHEMA,
      assertConfig: assertConfig,
      computeLabel: computeLabel,
    };
  }

  // The height of your card. Home Assistant uses this to automatically
  // distribute all cards over the available columns in masonry view
  getCardSize() {
    return 7;
  }

  // The rules for sizing your card in the grid in sections view
  getLayoutOptions() {
    return {
      grid_rows: 7,
      grid_columns: 20,
      grid_min_rows: 3,
      grid_max_rows: 10,
    };
  }
}

customElements.define("heat-pump-card", HeatPumpCard);

window.customCards = window.customCards || [];
window.customCards.push({
  type: "heat-pump-card",
  name: "Heat Pump Card",
  description: "A custom card displaying heat pump state"
});

/*
 * IP-Symcon adapter.
 * Sämtliche Darstellungslogik liegt in dieser Datei.
 */
window.SymconHeatPump = {
  init(payload) {
        let currentConfig = payload.config || {};
        let currentControls = payload.controls || {};
        let embeddedSvg = payload.svg || '';
        let embeddedLocalization = payload.localization || {};
        let currentData = payload.data || {};

        const toCompatibilityStates = (data) => {
            const states = {};

            Object.entries(data || {}).forEach(([key, item]) => {
                if (!item || typeof item !== 'object' || !('value' in item)) {
                    return;
                }

                states[key] = {
                    state: item.binary
                        ? (item.value ? 'on' : 'off')
                        : (item.value === null || typeof item.value === 'undefined'
                            ? ''
                            : String(item.value)),
                    attributes: {
                        unit_of_measurement: item.unit || ''
                    }
                };
            });

            return states;
        };

        let currentStates = toCompatibilityStates(currentData);

        const errorBox = document.getElementById('wp-error');

        const showError = (message) => {
            if (!errorBox) {
                return;
            }

            errorBox.textContent = message;
            errorBox.style.display = 'block';
        };

        const clearError = () => {
            if (!errorBox) {
                return;
            }

            errorBox.textContent = '';
            errorBox.style.display = 'none';
        };

        const HeatPumpClass = customElements.get('heat-pump-card');

        if (!HeatPumpClass) {
            showError('heat-pump-card.js wurde injiziert, aber das Custom Element "heat-pump-card" wurde nicht registriert.');
            return;
        }

        /*
         * Symcon-Adapter:
         * Die Originaldatei heat-pump-card.js bleibt unangetastet.
         * Nur die beiden Methoden, die in Home Assistant per XMLHttpRequest
         * SVG und Sprachdatei nachladen, werden zur Laufzeit auf die bereits
         * von PHP eingebetteten Ressourcen umgebogen.
         */
        HeatPumpClass.prototype.readSvg = function(lang, handleSvg, hass) {
            try {
                this.innerHTML =
                    '<ha-card>\\n'
                    + embeddedSvg
                        // heat-pump.svg is an XML file. In HTML-SDK it is inserted
                        // as inline SVG, so the XML CDATA wrapper around <style>
                        // must be removed while preserving the original CSS.
                        .replace(/<!\[CDATA\[/g, '')
                        .replace(/\]\]>/g, '')
                        .replace(/ class="rotate"/g, '')
                        .replace(/display: inline;/g, 'display: none;')
                    + '</ha-card>';

                this.content = this.querySelector('svg');

                if (!this.content) {
                    throw new Error('SVG konnte nicht in die Card eingefügt werden.');
                }

                // SVG immer in die vorhandene HTML-SDK-Kachel einpassen.
                this.content.setAttribute('width', '100%');
                this.content.setAttribute('height', '100%');
                this.content.setAttribute('preserveAspectRatio', 'xMidYMid meet');

                // Der Hintergrund gehört dem Symcon-Layout, nicht der Fremd-Card.
                // Inline-Styles übersteuern die im Original-SVG fest eingetragenen
                // weißen/schwarzen Hintergründe, ohne die Originaldatei zu ändern.
                this.content.style.setProperty('background', 'transparent', 'important');
                this.content.style.setProperty('background-color', 'transparent', 'important');
                this.content.style.setProperty('--card-background-color', 'transparent');

                // Text-/Linienfarbe möglichst aus dem aktuellen Layout übernehmen.
                const layoutTextColor = getComputedStyle(document.body).color;
                if (layoutTextColor) {
                    this.content.style.setProperty('--primary-text-color', layoutTextColor);
                }


                const details = this.content.querySelector('#linkDetails');
                if (details && typeof this.linkHandling === 'function') {
                    details.addEventListener('click', this.linkHandling);
                }

                const settings = this.content.querySelector('#linkSettings');
                if (settings && typeof this.linkHandling === 'function') {
                    settings.addEventListener('click', this.linkHandling);
                }

                this.readLocalization(lang, hass);
            } catch (error) {
                showError(
                    'Fehler beim Einfügen der Wärmepumpen-SVG:\\n'
                    + (error && error.stack ? error.stack : String(error))
                );
            }

            return embeddedSvg;
        };

        const originalTempColor = HeatPumpClass.prototype.tempColor;

        /*
         * Einheitliche Temperatur-Farbskala für die gesamte Hydraulik:
         * 20 / 30 / 40 / 50 / 60 / 70 °C.
         * Dazwischen wird stufenlos interpoliert.
         */
        const getTemperatureColorStops = () => {
            const configured = Array.isArray(currentConfig.temperatureColorScale)
                ? currentConfig.temperatureColorScale
                : [];

            const defaults = [
                {temperature: 20, color: 26316},
                {temperature: 30, color: 6733823},
                {temperature: 40, color: 16769126},
                {temperature: 50, color: 16752412},
                {temperature: 60, color: 15746116},
                {temperature: 70, color: 16711680}
            ];

            const intToRgb = (value, fallback) => {
                const number = Number(value);
                if (!Number.isFinite(number)) {
                    return fallback;
                }

                const color = Math.max(0, Math.min(0xFFFFFF, Math.trunc(number)));

                return [
                    (color >> 16) & 0xFF,
                    (color >> 8) & 0xFF,
                    color & 0xFF
                ];
            };

            const fallbackRgb = [
                [0, 102, 204],
                [102, 191, 255],
                [255, 224, 102],
                [255, 159, 28],
                [240, 68, 68],
                [255, 0, 0]
            ];

            const stops = defaults.map((fallback, index) => {
                const item = configured[index] || fallback;
                const temperature = Number(item.temperature);

                return [
                    Number.isFinite(temperature)
                        ? temperature
                        : fallback.temperature,
                    intToRgb(item.color, fallbackRgb[index])
                ];
            });

            /*
             * Die Temperaturwerte dürfen frei konfiguriert werden.
             * Für die Interpolation sortieren wir sie automatisch aufsteigend.
             */
            stops.sort((a, b) => a[0] - b[0]);

            return stops;
        };

        const rgbToHex = (rgb) =>
            '#' + rgb.map((component) =>
                Math.max(0, Math.min(255, Math.round(component)))
                    .toString(16)
                    .padStart(2, '0')
            ).join('');

        const temperatureColor = (temperature) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return null;
            }

            const value = Number(temperature);

            if (!Number.isFinite(value)) {
                return null;
            }

            const stops = getTemperatureColorStops();

            if (value <= stops[0][0]) {
                return rgbToHex(stops[0][1]);
            }

            const last = stops[stops.length - 1];
            if (value >= last[0]) {
                return rgbToHex(last[1]);
            }

            for (let i = 0; i < stops.length - 1; i++) {
                const [t1, c1] = stops[i];
                const [t2, c2] = stops[i + 1];

                if (value >= t1 && value <= t2) {
                    const factor = (value - t1) / (t2 - t1);

                    return rgbToHex(c1.map((component, index) =>
                        component + (c2[index] - component) * factor
                    ));
                }
            }

            return null;
        };

        const readStateNumber = (entity) => {
            if (!entity || !currentStates || !currentStates[entity]) {
                return null;
            }

            const raw = currentStates[entity].state;
            const normalized = String(raw ?? '')
                .trim()
                .replace(',', '.')
                .replace(/[^0-9+\-.]/g, '');

            const value = Number(normalized);
            return Number.isFinite(value) ? value : null;
        };

        /*
         * Die Speicher der Original-Card verwenden tempColor().
         * Dadurch gilt automatisch dieselbe Skala für Puffer und Warmwasser.
         */
        HeatPumpClass.prototype.tempColor = function(temp) {
            if (!currentConfig.useCustomTemperatureColors) {
                if (typeof originalTempColor === 'function') {
                    return originalTempColor.call(this, temp);
                }

                return '#ffffff00';
            }

            return temperatureColor(temp) || '#ffffff00';
        };

        HeatPumpClass.prototype.readLocalization = function(lang, hass) {
            try {
                HeatPumpClass.localization = embeddedLocalization;

                const texts =
                    embeddedLocalization
                    && embeddedLocalization.svgTexts
                        ? embeddedLocalization.svgTexts
                        : {};

                const setText = (selector, value) => {
                    const element = this.content ? this.content.querySelector(selector) : null;
                    if (element) {
                        element.innerHTML = value || '';
                    }
                };

                setText('#textTankWWName', texts.tankWWName);
                setText('#textTankHPName', texts.tankHPName);
                setText('#textEvaporator', texts.evaporator);
                setText('#textCondenser', texts.condenser);
                setText('#textCompressor', texts.compressor);
                setText('#textExpansionValve', texts.expansionValve);
                setText('#textCirculatingPump', texts.circulatingPump);
                setText('#textSupplyTemperatureLabel', texts.supplyTemperatureLabel);

                this.setConfig(this.config || {});
                this.setValues(hass || { language: 'de-DE', states: {} });

                // Die eigentliche Symcon-Zuordnung wird direkt nach der
                // Initialisierung nochmals durch applyCardData() gesetzt.
            } catch (error) {
                showError(
                    'Fehler beim Initialisieren der Wärmepumpen-Card:\\n'
                    + (error && error.stack ? error.stack : String(error))
                );
            }
        };

        const isEntityOn = (entityName) => {
            if (!entityName || !currentStates || !currentStates[entityName]) {
                return false;
            }

            const value = String(currentStates[entityName].state ?? '').trim().toLowerCase();
            return ['1', 'true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(value);
        };

        const setStroke = (svg, selector, value) => {
            const element = svg.querySelector(selector);
            if (element) {
                element.style.stroke = value;
            }
        };

        const applyRefrigerantCircuitMode = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const cooling = stateIsOn(currentConfig.heatingPumpCoolingMode);

            const getText = (selector) => {
                const el = svg.querySelector(selector);
                return el ? el.textContent : '';
            };

            const setText = (selector, value) => {
                const el = svg.querySelector(selector);
                if (el) {
                    el.textContent = value;
                }
            };

            /*
             * Reversibler Kältekreis:
             *
             * Heizbetrieb:
             *   Außenseite links  = Verdampfer / Niederdruck
             *   Wasserseite rechts = Verflüssiger / Hochdruck
             *
             * Kühlbetrieb:
             *   Außenseite links  = Verflüssiger / Hochdruck
             *   Wasserseite rechts = Verdampfer / Niederdruck
             *
             * Verdichter und Expansionsventil bleiben an ihrer Position.
             * Wir spiegeln NICHT die komplette SVG, damit Texte und Symbole
             * nicht seitenverkehrt werden. Stattdessen werden die Funktionen
             * der beiden Wärmetauscher samt Messwerten sauber vertauscht.
             */

            if (!cooling) {
                return;
            }

            const evaporatorLabel = getText('#textEvaporator');
            const evaporatorPressure = getText('#textEvaporatorPressure');
            const evaporatorTemperature = getText('#textEvaporatorTemperature');

            const condenserLabel = getText('#textCondenser');
            const condenserPressure = getText('#textCondenserPressure');
            const condenserTemperature = getText('#textCondenserTemperature');

            setText('#textEvaporator', condenserLabel || 'Kondensator');
            setText('#textEvaporatorPressure', condenserPressure);
            setText('#textEvaporatorTemperature', condenserTemperature);

            setText('#textCondenser', evaporatorLabel || 'Verdampfer');
            setText('#textCondenserPressure', evaporatorPressure);
            setText('#textCondenserTemperature', evaporatorTemperature);

            /*
             * Die Original-SVG hat eindeutige IDs für genau die beiden
             * Wärmetauscher-Symbole:
             *
             * Verdampfer:
             *   #pathHPModelEvaporatorSymbol001
             *   #pathHPModelEvaporatorSymbol002
             *
             * Verflüssiger:
             *   #pathHPModelCondenserSymbol
             *
             * Im Kühlbetrieb werden ausschließlich diese drei Pfade verschoben.
             * Verdichter, Expansionsventil, Pumpen, Leitungen und Texte bleiben
             * unangetastet.
             */
            const evaporatorSymbols = [
                svg.querySelector('#pathHPModelEvaporatorSymbol001'),
                svg.querySelector('#pathHPModelEvaporatorSymbol002')
            ].filter(Boolean);

            const condenserSymbol = svg.querySelector('#pathHPModelCondenserSymbol');

            if (
                evaporatorSymbols.length === 2
                && condenserSymbol
                && svg.dataset.symconHeatExchangerSymbolsSwapped !== '1'
            ) {
                const unionBox = (elements) => {
                    const boxes = elements.map((element) => element.getBBox());

                    const minX = Math.min(...boxes.map((box) => box.x));
                    const minY = Math.min(...boxes.map((box) => box.y));
                    const maxX = Math.max(...boxes.map((box) => box.x + box.width));
                    const maxY = Math.max(...boxes.map((box) => box.y + box.height));

                    return {
                        cx: (minX + maxX) / 2,
                        cy: (minY + maxY) / 2
                    };
                };

                const evaporatorCenter = unionBox(evaporatorSymbols);
                const condenserCenter = unionBox([condenserSymbol]);

                const evaporatorDx = condenserCenter.cx - evaporatorCenter.cx;
                const evaporatorDy = condenserCenter.cy - evaporatorCenter.cy;

                const condenserDx = evaporatorCenter.cx - condenserCenter.cx;
                const condenserDy = evaporatorCenter.cy - condenserCenter.cy;

                const movePath = (element, dx, dy) => {
                    const originalTransform = element.getAttribute('transform') || '';

                    element.setAttribute(
                        'transform',
                        'translate(' + dx + ' ' + dy + ')'
                        + (originalTransform ? ' ' + originalTransform : '')
                    );
                };

                evaporatorSymbols.forEach((element) => {
                    movePath(element, evaporatorDx, evaporatorDy);
                });

                movePath(condenserSymbol, condenserDx, condenserDy);

                svg.dataset.symconHeatExchangerSymbolsSwapped = '1';
            }

            svg.dataset.refrigerantCircuitDirection = 'reversed';
        };

        const applyCoolingVisualization = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const cooling = stateIsOn(currentConfig.heatingPumpCoolingMode);

            /*
             * WICHTIG:
             * Verdampfer/Kondensator und ihre Messwerte bleiben unverändert.
             * Die Original-Card kennt keine hydraulische Umschaltung im Kühlbetrieb.
             *
             * Wir ändern deshalb ausschließlich die Gebäudeseite:
             *
             * Heizen:
             *   Vorlauf  = warm / rot
             *   Rücklauf = kalt / blau
             *
             * Kühlen:
             *   Vorlauf  = kalt / blau
             *   Rücklauf = wärmer / rot
             */
            const supplyColor = cooling ? '#0a84ff' : '#ff3b30';
            const returnColor = cooling ? '#ff3b30' : '#0a84ff';

            const setStrokeColor = (selector, color) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('stroke', color, 'important');
                }
            };

            const setFillColor = (selector, color) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('fill', color, 'important');
                }
            };

            // Heizwasserseite / Puffer / Heizkreise.
            // Selektoren, die in einer bestimmten Topologie nicht existieren,
            // werden einfach übersprungen.
            [
                '#pathPipeToBuffer',
                '#pathPipeToHeatingCircuitPump',
                '#pathPipeToHeatingCircuitPump2',
                '#pathPipeToHeatingCircuitPump3',
                '#pathPipeBufferToHeating',
                '#pathPipeTankHPToHeating',
                '#pathPipeHeatingSupply',
                '#pathPipeHeatingSupply2',
                '#pathPipeHeatingSupply3'
            ].forEach((selector) => setStrokeColor(selector, supplyColor));

            [
                '#pathPipeFromBuffer',
                '#pathPipeToHP',
                '#pathPipeToHP2',
                '#pathPipeHeatingToBuffer',
                '#pathPipeHeatingReturn',
                '#pathPipeHeatingReturn2',
                '#pathPipeHeatingReturn3'
            ].forEach((selector) => setStrokeColor(selector, returnColor));

            // Falls die Heizkreise ihre Farben über SVG-Verläufe beziehen,
            // diese im Kühlbetrieb ebenfalls umkehren.
            const gradientPairs = [
                ['#stopCircuit1', '#stopCircuit2'],
                ['#stopCircuit3', '#stopCircuit4'],
                ['#stopCircuit5', '#stopCircuit6']
            ];

            gradientPairs.forEach(([supplyStopSelector, returnStopSelector]) => {
                const supplyStop = svg.querySelector(supplyStopSelector);
                const returnStop = svg.querySelector(returnStopSelector);

                if (supplyStop) {
                    supplyStop.style.setProperty('stop-color', supplyColor, 'important');
                }

                if (returnStop) {
                    returnStop.style.setProperty('stop-color', returnColor, 'important');
                }
            });

            // Heizkreis-Symbole optional leicht an den Modus anpassen, ohne
            // die originale Geometrie/Topologie zu verändern.
            if (cooling) {
                [
                    '#gHeaterCircuit1',
                    '#gHeaterCircuit2',
                    '#gHeaterCircuit3'
                ].forEach((selector) => {
                    const group = svg.querySelector(selector);
                    if (group) {
                        group.dataset.symconCooling = '1';
                    }
                });
            } else {
                [
                    '#gHeaterCircuit1',
                    '#gHeaterCircuit2',
                    '#gHeaterCircuit3'
                ].forEach((selector) => {
                    const group = svg.querySelector(selector);
                    if (group) {
                        delete group.dataset.symconCooling;
                    }
                });
            }

            // Nur als interner Marker für spätere Erweiterungen.
            svg.dataset.operatingMode = cooling ? 'cooling' : 'heating';
        };

        const formatStateForSvg = (entityName) => {
            if (!entityName || !currentStates || !currentStates[entityName]) {
                return '';
            }

            const item = currentStates[entityName];
            const raw = item.state;

            if (raw === null || raw === undefined || raw === '') {
                return '';
            }

            const numeric = Number(raw);
            const unit =
                item.attributes && item.attributes.unit_of_measurement
                    ? String(item.attributes.unit_of_measurement).trim()
                    : '';

            if (!Number.isNaN(numeric)) {
                const value = new Intl.NumberFormat('de-CH', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                }).format(numeric);

                return unit ? value + ' ' + unit : value;
            }

            return unit ? String(raw) + ' ' + unit : String(raw);
        };

        const updateRefrigerantValues = (card) => {
            if (!card || !card.content) {
                return;
            }

            const values = [
                ['#textEvaporatorPressure', currentConfig.evaporatorPressure],
                ['#textEvaporatorTemperature', currentConfig.evaporatorTemperature],
                ['#textCondenserPressure', currentConfig.condenserPressure],
                ['#textCondenserTemperature', currentConfig.condenserTemperature],
                ['#textExpansionValveOpening', currentConfig.expansionValveOpening],
                ['#textCompressorValue', currentConfig.compressorValue]
            ];

            values.forEach(([selector, entityName]) => {
                const element = card.content.querySelector(selector);
                if (element) {
                    element.textContent = formatStateForSvg(entityName);
                }
            });
        };

        const resolveLayoutTextColor = () => {
            // Im HTML-SDK erbt document.body die Textfarbe des übergeordneten
            // Symcon-Layouts nicht zuverlässig. Der Farbschemamodus dagegen wird
            // vom Browser an die Kachel weitergereicht.
            const dark = window.matchMedia
                && window.matchMedia('(prefers-color-scheme: dark)').matches;

            return dark ? '#ffffff' : '#000000';
        };

        const isBlack = (value) => {
            const normalized = String(value || '')
                .trim()
                .toLowerCase()
                .replace(/\s+/g, '');

            return [
                'black',
                '#000',
                '#000000',
                'rgb(0,0,0)',
                'rgba(0,0,0,1)'
            ].includes(normalized);
        };

        const applyThemeColors = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const textColor = resolveLayoutTextColor();

            // Hintergrund ausschließlich vom Symcon-Layout.
            svg.style.setProperty('background', 'transparent', 'important');
            svg.style.setProperty('background-color', 'transparent', 'important');
            svg.style.setProperty('--card-background-color', 'transparent');
            svg.style.setProperty('--primary-text-color', textColor);
            svg.style.setProperty('color', textColor);

            // Text grundsätzlich an den Layoutmodus koppeln.
            svg.querySelectorAll('text, tspan').forEach((element) => {
                element.style.setProperty('fill', textColor, 'important');
                element.style.setProperty('color', textColor, 'important');
            });

            // Schwarze Linien/Symbole der Original-SVG im Dark Mode weiß machen.
            // Im Light Mode bleiben sie schwarz.
            svg.querySelectorAll('*').forEach((element) => {
                const computed = getComputedStyle(element);

                const fill =
                    element.getAttribute('fill')
                    || element.style.fill
                    || computed.fill;

                const stroke =
                    element.getAttribute('stroke')
                    || element.style.stroke
                    || computed.stroke;

                if (isBlack(fill)) {
                    element.style.setProperty('fill', textColor, 'important');
                }

                if (isBlack(stroke)) {
                    element.style.setProperty('stroke', textColor, 'important');
                }
            });
        };

        const rebuildCard = () => {
            const root = document.getElementById('wp-root');

            if (!root) {
                showError('Der Wärmepumpen-Container wurde im HTML nicht gefunden.');
                return;
            }

            closeModeMenu();

            const oldCard = document.getElementById('wp-card');
            const newCard = document.createElement('heat-pump-card');
            newCard.id = 'wp-card';

            if (oldCard) {
                oldCard.replaceWith(newCard);
            } else {
                const menu = document.getElementById('wp-mode-menu');
                if (menu) {
                    root.insertBefore(newCard, menu);
                } else {
                    root.appendChild(newCard);
                }
            }

            requestAnimationFrame(() => {
                queueMicrotask(() => applyCardData());
            });
        };

        const stateIsOn = (entityName) => {
            return !!(
                entityName
                && currentStates
                && currentStates[entityName]
                && String(currentStates[entityName].state).toLowerCase() === 'on'
            );
        };

        const closeModeMenu = () => {
            const menu = document.getElementById('wp-mode-menu');
            if (menu) {
                menu.style.display = 'none';
                menu.innerHTML = '';
            }
        };

        const openModeMenu = (functionName, event) => {
            event.preventDefault();
            event.stopPropagation();

            const control = currentControls && currentControls[functionName];
            if (!control || !control.configured || !Array.isArray(control.options) || control.options.length === 0) {
                return;
            }

            const menu = document.getElementById('wp-mode-menu');
            if (!menu) {
                return;
            }

            const titles = {
                heating: 'Heizen',
                hotwater: 'Warmwasser',
                cooling: 'Kühlen'
            };

            menu.innerHTML = '';

            const title = document.createElement('div');
            title.className = 'wp-mode-title';
            title.textContent = titles[functionName] || functionName;
            menu.appendChild(title);

            const valuesEqual = (left, right) => {
                const leftNumber = Number(left);
                const rightNumber = Number(right);

                if (!Number.isNaN(leftNumber) && !Number.isNaN(rightNumber)) {
                    return leftNumber === rightNumber;
                }

                return String(left) === String(right);
            };

            control.options.forEach((option) => {
                const button = document.createElement('button');
                button.type = 'button';

                const selected = valuesEqual(option.value, control.currentValue);

                button.textContent = (selected ? '✓ ' : '   ') + option.name;
                button.dataset.value = String(option.value);

                if (selected) {
                    button.classList.add('active');
                    button.setAttribute('aria-current', 'true');
                }

                button.addEventListener('click', (clickEvent) => {
                    clickEvent.preventDefault();
                    clickEvent.stopPropagation();

                    requestAction(
                        'SetControlMode',
                        JSON.stringify({
                            function: functionName,
                            value: option.value
                        })
                    );

                    closeModeMenu();
                });

                menu.appendChild(button);
            });

            const x = Math.min(event.clientX + 8, window.innerWidth - 310);
            const y = Math.min(event.clientY + 8, window.innerHeight - 260);
            menu.style.left = Math.max(8, x) + 'px';
            menu.style.top = Math.max(8, y) + 'px';
            menu.style.display = 'block';
        };

        const setIconColor = (group, color) => {
            if (!group) {
                return;
            }

            group.querySelectorAll('path, rect, circle, line, polyline, polygon, use').forEach((element) => {
                const computed = getComputedStyle(element);

                if (computed.fill && computed.fill !== 'none' && computed.fill !== 'rgba(0, 0, 0, 0)') {
                    element.style.setProperty('fill', color, 'important');
                }

                if (computed.stroke && computed.stroke !== 'none' && computed.stroke !== 'rgba(0, 0, 0, 0)') {
                    element.style.setProperty('stroke', color, 'important');
                }
            });
        };

        const applyWWValvePipeGeometry = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            const pipeToBuffer = svg.querySelector('#pathPipeToBuffer');
            const pipeFromBuffer = svg.querySelector('#pathPipeFromBuffer');
            const pipeHotCold = svg.querySelector('#pathPipeHotColdHeatpump');
            const pipeHotWater = svg.querySelector('#pathPipeHotWaterToTank');

            /*
             * Das Umschaltventil sitzt bei (620 / 450), Radius 30.
             * Es gibt drei Leitungsstücke, die in den Ventilbereich hineinlaufen:
             *
             * 1. pathPipeToBuffer          -> nach oben
             * 2. pathPipeHotColdHeatpump  -> von links
             * 3. pathPipeHotWaterToTank   -> vom Warmwasserspeicher
             *
             * Nur diese drei Endstücke werden am Ventilrand gekürzt.
             */

            if (pipeToBuffer) {
                const original =
                    'm 598,450 c 22,0 22,-15 22,-30 V 158 c 0,-12 5,-18 18,-18 H 750';

                const withValve =
                    'M 620,420 V 158 c 0,-12 5,-18 18,-18 H 750';

                pipeToBuffer.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }

            if (pipeFromBuffer) {
                const original =
                    'm 997,265 c 0,15 0,30 -22,30 H 650 c -15,0 -22,13 -22,30 v 170 c 0,18 -18,30 -30,30';

                /*
                 * Blaue Rücklaufleitung:
                 * Der senkrechte Abschnitt liegt bei x=628 und läuft damit
                 * durch den Ventilkreis (Mittelpunkt 620/450, r=30).
                 * Nur der Bereich innerhalb des Kreises wird ausgespart.
                 */
                const withValve =
                    'm 997,265 c 0,15 0,30 -22,30 H 650 c -15,0 -22,13 -22,30 V 420 '
                    + 'M 628,480 V 495 c 0,18 -18,30 -30,30';

                pipeFromBuffer.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }

            if (pipeHotCold) {
                const original =
                    'm 598,525 h -85 c -15,0 -30,0 -35,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 h 90';

                /*
                 * Original endet bei x=598 / y=450 und läuft damit in den
                 * Ventilkreis hinein. Mit Ventil endet der letzte horizontale
                 * Abschnitt bereits bei x=590, also exakt am linken Kreisrand.
                 */
                const withValve =
                    'm 598,525 h -85 c -15,0 -30,0 -35,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 15,0 30,0 30,10 0,5 -15,5 -30,5 -15,0 -30,0 -30,-15 0,-15 15,-15 30,-15 h 82';

                pipeHotCold.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }

            if (pipeHotWater) {
                const original =
                    'm 598,525 h 95 c 15,0 30,0 35,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 H 598';

                /*
                 * Dieser Pfad kommt von rechts aus Richtung Warmwasserspeicher.
                 * Mit Ventil endet er deshalb am rechten Kreisrand bei x=650.
                 */
                const withValve =
                    'm 598,525 h 95 c 15,0 30,0 35,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 -15,0 -30,0 -30,10 0,5 15,5 30,5 15,0 30,0 30,-15 0,-15 -15,-15 -30,-15 H 650';

                pipeHotWater.setAttribute(
                    'd',
                    currentConfig.wwHeatingValve ? withValve : original
                );
            }
        };

        const storedCircuitColorKey = 'symconHeatPumpStoredCircuitColors';

        const loadStoredCircuitColors = () => {
            try {
                const raw = window.sessionStorage
                    ? window.sessionStorage.getItem(storedCircuitColorKey)
                    : null;

                return raw ? JSON.parse(raw) : {};
            } catch (error) {
                return {};
            }
        };

        const saveStoredCircuitColors = (colors) => {
            try {
                if (window.sessionStorage) {
                    window.sessionStorage.setItem(
                        storedCircuitColorKey,
                        JSON.stringify(colors)
                    );
                }
            } catch (error) {
                // Speicherung ist nur Komfort; Darstellung funktioniert auch ohne.
            }
        };

        let storedCircuitColors = loadStoredCircuitColors();

        const restoreOriginalTemperatureColors = (card) => {
            if (!card || !card.content || currentConfig.useCustomTemperatureColors) {
                return;
            }

            const svg = card.content;

            /*
             * Entfernt ausschließlich unsere Inline-Überschreibungen.
             * Danach greifen wieder die im Original-SVG/der Original-Card
             * definierten Farben und Gradienten.
             */
            [
                '#pathPipeRefluxWW',
                '#pathPipeToCirculatingPump',
                '#pathPipeHotColdHeatpump',
                '#pathPipeToBuffer',
                '#pathPipeFromBuffer',
                '#pathPipeHotWaterToTank',
                '#pathPipeToHeatingCircuitPump',
                '#pathPipeToHeatingCircuitPump2',
                '#pathPipeToHeatingCircuitPump3',
                '#pathPipeToHP',
                '#pathPipeToHP2',
                '#pathPipeBufferToHeating',
                '#pathPipeHeatingToBuffer',
                '#pathHPModelCondenserSymbol',
                '#pathHPModelEvaporatorSymbol001',
                '#pathHPModelEvaporatorSymbol002',
                '#pathUnderfloorHeating1',
                '#pathUnderfloorHeating2',
                '#pathUnderfloorHeating3',
                '#pathRadiatorPipeIn1',
                '#pathRadiatorPipeIn2',
                '#pathRadiatorPipeIn3',
                '#pathRadiatorPipeOut1',
                '#pathRadiatorPipeOut2',
                '#pathRadiatorPipeOut3',
                '#rectRadiator1',
                '#rectRadiator2',
                '#rectRadiator3',
                '#pathTankWWChassis',
                '#pathTankHPChassis'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);
                if (!element) {
                    return;
                }

                element.style.removeProperty('stroke');
                element.style.removeProperty('stroke-opacity');
                element.style.removeProperty('fill');
                element.style.removeProperty('fill-opacity');
            });

            /*
             * Boiler-Wendel wieder an den Originalgradienten hängen.
             */
            const boilerCoil = svg.querySelector('#pathPipeHotWaterToTank');
            if (boilerCoil) {
                boilerCoil.style.removeProperty('display');
                boilerCoil.style.removeProperty('visibility');
                boilerCoil.style.removeProperty('stroke-width');
                boilerCoil.style.removeProperty('stroke-opacity');
                boilerCoil.style.setProperty(
                    'stroke',
                    'url(#linearGradientPipe1)'
                );
            }

            const outline = svg.querySelector('#symconBoilerCoilOutline');
            if (outline && outline.parentNode) {
                outline.parentNode.removeChild(outline);
            }

            const customGradient = svg.querySelector('#symconLinearGradientBoilerCoil');
            if (customGradient && customGradient.parentNode) {
                customGradient.parentNode.removeChild(customGradient);
            }
        };

        const applyTemperatureColorOpacity = (card) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return;
            }

            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Die Original-Card verwendet bei Speicher und Hydraulik teilweise
             * 50 % Deckkraft. Das verfälscht konfigurierte Temperaturfarben auf
             * dunklem Hintergrund erheblich (Rot wirkt z.B. braun).
             *
             * Temperaturfarben deshalb überall mit voller Deckkraft darstellen.
             */
            [
                '#pathTankWWChassis',
                '#pathTankHPChassis'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('fill-opacity', '1', 'important');
                }
            });

            [
                '#pathPipeRefluxWW',
                '#pathPipeToCirculatingPump',
                '#pathPipeHotColdHeatpump',
                '#pathPipeToBuffer',
                '#pathPipeFromBuffer',
                '#pathPipeHotWaterToTank',
                '#pathPipeToHeatingCircuitPump',
                '#pathPipeToHeatingCircuitPump2',
                '#pathPipeToHeatingCircuitPump3',
                '#pathPipeToHP',
                '#pathPipeToHP2',
                '#pathPipeBufferToHeating',
                '#pathPipeHeatingToBuffer'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);
                if (element) {
                    element.style.setProperty('stroke-opacity', '1', 'important');
                }
            });
        };

        const applyHeatingCircuitTemperatureColors = (card) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return;
            }

            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Hydraulische Stellung ausschließlich über das konfigurierte
             * Umschaltventil Warmwasser/Heizung bestimmen.
             *
             * Ventil aktiv = Boiler/Warmwasser
             * Ventil inaktiv = Heizkreis
             *
             * Ist KEIN Ventil konfiguriert, gibt es keine unterscheidbare
             * hydraulische Stellung. Dann verwenden Heizungs- und Boilerseite
             * denselben Vorlauf-/Rücklauf-Farbverlauf.
             */
            const valveConfigured = !!currentConfig.wwHeatingValve;
            const hotWaterActive =
                valveConfigured && stateIsOn(currentConfig.wwHeatingValve);

            const circuits = [
                {
                    key: '1',
                    gradient: 'linearGradientCircuit1',
                    supply: currentConfig.supplyTemperatureHeating,
                    reflux: currentConfig.refluxTemperatureHeating,
                    supplyPipes: ['#pathPipeToHeatingCircuitPump'],
                    refluxPipes: ['#pathPipeToHP']
                },
                {
                    key: '2',
                    gradient: 'linearGradientCircuit2',
                    supply: currentConfig.supplyTemperatureHeating2,
                    reflux: currentConfig.refluxTemperatureHeating2,
                    supplyPipes: ['#pathPipeToHeatingCircuitPump2'],
                    refluxPipes: ['#pathPipeToHP2']
                },
                {
                    key: '3',
                    gradient: 'linearGradientCircuit3',
                    supply: currentConfig.supplyTemperatureHeating3,
                    reflux: currentConfig.refluxTemperatureHeating3,
                    supplyPipes: ['#pathPipeToHeatingCircuitPump3'],
                    refluxPipes: []
                }
            ];

            const setStrokeColor = (selectors, color) => {
                if (!color) {
                    return;
                }

                selectors.forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element) {
                        element.style.setProperty('stroke', color, 'important');
                        element.style.setProperty('stroke-opacity', '1', 'important');
                    }
                });
            };

            const setFillColor = (selectors, color) => {
                if (!color) {
                    return;
                }

                selectors.forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element) {
                        element.style.setProperty('fill', color, 'important');
                        element.style.setProperty('fill-opacity', '1', 'important');
                    }
                });
            };

            const setGradient = (gradientId, supplyColor, refluxColor) => {
                const gradient = svg.querySelector('#' + gradientId);
                if (!gradient || !supplyColor || !refluxColor) {
                    return;
                }

                const stops = gradient.querySelectorAll('stop');
                if (stops.length < 2) {
                    return;
                }

                stops[0].style.setProperty('stop-color', supplyColor, 'important');
                stops[0].setAttribute('stop-color', supplyColor);

                stops[stops.length - 1].style.setProperty(
                    'stop-color',
                    refluxColor,
                    'important'
                );
                stops[stops.length - 1].setAttribute('stop-color', refluxColor);
            };

            const setGradientStops = (gradient, color1, color2) => {
                if (!gradient || !color1 || !color2) {
                    return;
                }

                const stops = gradient.querySelectorAll('stop');
                if (stops.length < 2) {
                    return;
                }

                stops[0].style.setProperty('stop-color', color1, 'important');
                stops[0].setAttribute('stop-color', color1);

                stops[stops.length - 1].style.setProperty(
                    'stop-color',
                    color2,
                    'important'
                );
                stops[stops.length - 1].setAttribute('stop-color', color2);
            };

            const setHeatPumpCoilGradient = (hotColor, coolColor) => {
                const gradient = svg.querySelector('#linearGradientPipe1');
                setGradientStops(gradient, hotColor, coolColor);

                const heatPumpCoil = svg.querySelector('#pathPipeHotColdHeatpump');
                if (heatPumpCoil) {
                    heatPumpCoil.style.setProperty(
                        'stroke',
                        'url(#linearGradientPipe1)',
                        'important'
                    );
                    heatPumpCoil.style.setProperty(
                        'stroke-opacity',
                        '1',
                        'important'
                    );
                }
            };

            const ensureBoilerCoilGradient = () => {
                let gradient = svg.querySelector('#symconLinearGradientBoilerCoil');
                if (gradient) {
                    return gradient;
                }

                const original = svg.querySelector('#linearGradientPipe1');
                if (!original) {
                    return null;
                }

                gradient = original.cloneNode(true);
                gradient.setAttribute('id', 'symconLinearGradientBoilerCoil');

                gradient.querySelectorAll('stop').forEach((stop, index) => {
                    stop.setAttribute(
                        'id',
                        'symconBoilerCoilStop' + (index + 1)
                    );
                });

                original.parentNode.appendChild(gradient);
                return gradient;
            };

            const setBoilerCoilGradient = (hotColor, coolColor) => {
                const gradient = ensureBoilerCoilGradient();
                setGradientStops(gradient, hotColor, coolColor);

                const boilerCoil = svg.querySelector('#pathPipeHotWaterToTank');
                if (!boilerCoil) {
                    return;
                }

                /*
                 * Die Original-Card blendet diese Wendel nicht abhängig vom
                 * Ventil aus. Bei gleicher Farbe von Boiler und Wendel würde sie
                 * aber optisch vollständig im Speicher verschwinden.
                 *
                 * Deshalb eine schmale Kontur UNTER der eigentlichen Wendel.
                 * Die sichtbare Wendel selbst behält exakt ihre Temperaturfarbe.
                 */
                let outline = svg.querySelector('#symconBoilerCoilOutline');

                if (!outline) {
                    outline = boilerCoil.cloneNode(false);
                    outline.setAttribute('id', 'symconBoilerCoilOutline');
                    boilerCoil.parentNode.insertBefore(outline, boilerCoil);
                }

                const dark = window.matchMedia
                    && window.matchMedia('(prefers-color-scheme: dark)').matches;

                outline.style.setProperty(
                    'stroke',
                    dark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.55)',
                    'important'
                );
                outline.style.setProperty('stroke-width', '7', 'important');
                outline.style.setProperty('stroke-opacity', '1', 'important');
                outline.style.setProperty('fill', 'none', 'important');
                outline.style.setProperty('display', 'inline', 'important');
                outline.style.setProperty('visibility', 'visible', 'important');

                boilerCoil.style.setProperty(
                    'stroke',
                    'url(#symconLinearGradientBoilerCoil)',
                    'important'
                );
                boilerCoil.style.setProperty('stroke-width', '5', 'important');
                boilerCoil.style.setProperty('stroke-opacity', '1', 'important');
                boilerCoil.style.setProperty('fill', 'none', 'important');
                boilerCoil.style.setProperty('display', 'inline', 'important');
                boilerCoil.style.setProperty('visibility', 'visible', 'important');
            };

            let firstHeatingColors = null;
            let storedChanged = false;

            circuits.forEach((circuit) => {
                const supplyTemperature = readStateNumber(circuit.supply);
                const refluxTemperature = readStateNumber(circuit.reflux);

                let colors = storedCircuitColors[circuit.key] || null;

                /*
                 * Nur im Heizbetrieb werden die gespeicherten Heizkreisfarben
                 * aktualisiert. Beim Umschalten auf Warmwasser bleiben Heizkörper/
                 * Fußbodenheizung optisch auf ihrem letzten Heiz-Zustand stehen.
                 */
                if (
                    !hotWaterActive
                    && supplyTemperature !== null
                    && refluxTemperature !== null
                ) {
                    colors = {
                        supply: temperatureColor(supplyTemperature),
                        reflux: temperatureColor(refluxTemperature)
                    };

                    storedCircuitColors[circuit.key] = colors;
                    storedChanged = true;
                }

                /*
                 * Falls die Seite erstmals während Warmwasserladung geöffnet wird
                 * und noch nichts gespeichert ist, nehmen wir einmal die aktuell
                 * vorhandenen Werte als Startzustand.
                 */
                if (
                    !colors
                    && supplyTemperature !== null
                    && refluxTemperature !== null
                ) {
                    colors = {
                        supply: temperatureColor(supplyTemperature),
                        reflux: temperatureColor(refluxTemperature)
                    };
                }

                if (!colors || !colors.supply || !colors.reflux) {
                    return;
                }

                if (!firstHeatingColors) {
                    firstHeatingColors = colors;
                }

                setGradient(circuit.gradient, colors.supply, colors.reflux);

                [
                    '#pathUnderfloorHeating' + circuit.key,
                    '#pathRadiatorPipeIn' + circuit.key,
                    '#pathRadiatorPipeOut' + circuit.key
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element) {
                        element.style.setProperty('stroke-opacity', '1', 'important');
                    }
                });

                const radiator = svg.querySelector('#rectRadiator' + circuit.key);
                if (radiator) {
                    radiator.style.setProperty('stroke-opacity', '1', 'important');
                    radiator.style.setProperty('fill-opacity', '1', 'important');
                }

                setStrokeColor(circuit.supplyPipes, colors.supply);
                setStrokeColor(circuit.refluxPipes, colors.reflux);
            });

            if (storedChanged) {
                saveStoredCircuitColors(storedCircuitColors);
            }

            /*
             * Gemeinsame Hydraulik am Umschaltventil:
             *
             * Heizung aktiv:
             *   letzte/aktuelle Heizkreisfarbe.
             *
             * Warmwasser aktiv:
             *   die Wärmepumpen-Vorlauftemperatur färbt die Leitung zum Boiler
             *   und die Heizwendel im Boiler. Die Heizkreisfarben bleiben stehen.
             */
            if (hotWaterActive) {
                /*
                 * Während der Warmwasserladung bleiben die Heizkreis-Symbole auf
                 * ihren gespeicherten Heizfarben. Für Wärmepumpe und Boiler-Wendel
                 * verwenden wir dagegen die AKTUELLEN Vorlauf-/Rücklaufwerte.
                 *
                 * Priorität:
                 *   Heizkreis 1 -> Heizkreis 2 -> Heizkreis 3 -> WP-Vorlauf
                 */
                const currentSupplyTemperature =
                    readStateNumber(currentConfig.supplyTemperatureHeating)
                    ?? readStateNumber(currentConfig.supplyTemperatureHeating2)
                    ?? readStateNumber(currentConfig.supplyTemperatureHeating3)
                    ?? readStateNumber(currentConfig.supplyTemperature);

                const currentRefluxTemperature =
                    readStateNumber(currentConfig.refluxTemperatureHeating)
                    ?? readStateNumber(currentConfig.refluxTemperatureHeating2)
                    ?? readStateNumber(currentConfig.refluxTemperatureHeating3);

                const boilerTemperature =
                    readStateNumber(currentConfig.tankTempWWUp)
                    ?? readStateNumber(currentConfig.tankTempWWMiddle)
                    ?? readStateNumber(currentConfig.tankTempWWDown);

                const hotColor =
                    temperatureColor(currentSupplyTemperature)
                    || temperatureColor(boilerTemperature);

                const refluxColor =
                    temperatureColor(currentRefluxTemperature)
                    || temperatureColor(boilerTemperature)
                    || hotColor;

                const boilerColor =
                    temperatureColor(boilerTemperature)
                    || hotColor;

                /*
                 * Warmwasserleitung aus dem Boiler zum Zapfhahn:
                 * immer mit der aktuellen Boilertemperaturfarbe darstellen.
                 */
                setStrokeColor(
                    ['#pathPipeToCirculatingPump'],
                    boilerColor
                );

                /*
                 * Wärmepumpen-Wendel:
                 * aktuelle Vorlauf-/Rücklauftemperatur, unabhängig vom
                 * separaten Boiler-Gradienten.
                 */
                if (hotColor && refluxColor) {
                    setHeatPumpCoilGradient(
                        hotColor,
                        refluxColor
                    );
                }

                /*
                 * Warmwasserladung aktiv:
                 * Boiler-Wendel = aktuelle Vorlauf-/Rücklauftemperatur.
                 */
                setBoilerCoilGradient(
                    hotColor,
                    refluxColor
                );

                // Heizseite während Boilerladung vollständig eingefroren lassen.
                if (firstHeatingColors) {
                    setStrokeColor(
                        ['#pathPipeToBuffer'],
                        firstHeatingColors.supply
                    );
                    setStrokeColor(
                        [
                            '#pathPipeFromBuffer',
                            '#pathPipeToHP',
                            '#pathPipeToHP2'
                        ],
                        firstHeatingColors.reflux
                    );
                }

                /*
                 * Die Wärmeübertrager-Symbole in der Wärmepumpe übernehmen
                 * ebenfalls die gemeinsame Temperaturskala.
                 */
                const condenserTemperature =
                    readStateNumber(currentConfig.condenserTemperature);
                const evaporatorTemperature =
                    readStateNumber(currentConfig.evaporatorTemperature);

                const condenserColor = temperatureColor(condenserTemperature);
                const evaporatorColor = temperatureColor(evaporatorTemperature);

                setFillColor(
                    ['#pathHPModelCondenserSymbol'],
                    condenserColor
                );
                setStrokeColor(
                    [
                        '#pathHPModelEvaporatorSymbol001',
                        '#pathHPModelEvaporatorSymbol002'
                    ],
                    evaporatorColor
                );

                // Zusätzliche Boilerfarbe am Speicher-Heizstab, falls vorhanden.
                if (boilerColor) {
                    const heaterRodWW = svg.querySelector('#pathHeaterRodWW');
                    if (heaterRodWW) {
                        heaterRodWW.style.setProperty(
                            'stroke',
                            boilerColor,
                            'important'
                        );
                    }
                }

                return;
            }

            /*
             * Heizbetrieb: gemeinsame Leitungen folgen wieder dem Heizkreis.
             */
            if (firstHeatingColors) {
                setStrokeColor(
                    ['#pathPipeToBuffer'],
                    firstHeatingColors.supply
                );
                setStrokeColor(
                    [
                        '#pathPipeFromBuffer',
                        '#pathPipeToHP',
                        '#pathPipeToHP2'
                    ],
                    firstHeatingColors.reflux
                );

                setHeatPumpCoilGradient(
                    firstHeatingColors.supply,
                    firstHeatingColors.reflux
                );
            }

            /*
             * Warmwasserleitung aus dem Boiler zum Zapfhahn:
             * unabhängig vom Betriebsmodus mit der aktuellen Boilertemperatur.
             */
            const boilerTemperature =
                readStateNumber(currentConfig.tankTempWWUp)
                ?? readStateNumber(currentConfig.tankTempWWMiddle)
                ?? readStateNumber(currentConfig.tankTempWWDown);

            const boilerColor = temperatureColor(boilerTemperature);

            setStrokeColor(
                ['#pathPipeToCirculatingPump'],
                boilerColor
            );

            /*
             * Kein Umschaltventil konfiguriert:
             * Boiler- und Heizungsseite verwenden denselben Vorlauf-/Rücklauf-
             * Farbverlauf. So entstehen ohne Ventil keine widersprüchlichen
             * Farben für dieselbe hydraulische Verbindung.
             *
             * Ventil konfiguriert und auf Heizung:
             * Boiler-Wendel zeigt die aktuelle Boilertemperaturfarbe.
             */
            if (!valveConfigured && firstHeatingColors) {
                setBoilerCoilGradient(
                    firstHeatingColors.supply,
                    firstHeatingColors.reflux
                );
            } else if (boilerColor) {
                setBoilerCoilGradient(
                    boilerColor,
                    boilerColor
                );
            }

            /*
             * Wärmepumpen-Wärmetauscher ebenfalls immer mit Temperaturfarbe.
             */
            const condenserTemperature =
                readStateNumber(currentConfig.condenserTemperature);
            const evaporatorTemperature =
                readStateNumber(currentConfig.evaporatorTemperature);

            setFillColor(
                ['#pathHPModelCondenserSymbol'],
                temperatureColor(condenserTemperature)
            );
            setStrokeColor(
                [
                    '#pathHPModelEvaporatorSymbol001',
                    '#pathHPModelEvaporatorSymbol002'
                ],
                temperatureColor(evaporatorTemperature)
            );
        };

        const applyHeatingReturnContinuity = (card) => {
            if (!currentConfig.useCustomTemperatureColors) {
                return;
            }

            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            const hotWaterActive =
                !!currentConfig.wwHeatingValve
                && stateIsOn(currentConfig.wwHeatingValve);

            const readFirstNumber = (entities) => {
                for (const entity of entities) {
                    const value = readStateNumber(entity);
                    if (value !== null) {
                        return value;
                    }
                }

                return null;
            };

            let refluxColor = null;

            if (hotWaterActive) {
                /*
                 * Während Warmwasserladung die zuletzt gespeicherte Heizfarbe
                 * verwenden, damit der Heizkreis optisch eingefroren bleibt.
                 */
                const stored =
                    storedCircuitColors['1']
                    || storedCircuitColors['2']
                    || storedCircuitColors['3'];

                refluxColor = stored && stored.reflux
                    ? stored.reflux
                    : null;
            } else {
                const refluxTemperature = readFirstNumber([
                    currentConfig.refluxTemperatureHeating,
                    currentConfig.refluxTemperatureHeating2,
                    currentConfig.refluxTemperatureHeating3
                ]);

                refluxColor = temperatureColor(refluxTemperature);
            }

            if (!refluxColor) {
                return;
            }

            /*
             * Diese SVG-Elemente bilden die sichtbare Rücklaufkette rechts.
             * Besonders pathPipeToHP2 ist das kurze Teilstück, das sonst gerne
             * in der ursprünglichen blauen Card-Farbe stehen bleibt.
             */
            [
                '#pathPipeFromBuffer',
                '#pathPipeToHP',
                '#pathPipeToHP2'
            ].forEach((selector) => {
                const element = svg.querySelector(selector);

                if (!element) {
                    return;
                }

                element.setAttribute('stroke', refluxColor);
                element.style.setProperty('stroke', refluxColor, 'important');
            });
        };

        const storedHeatingTemperatureKey =
            'symconHeatPumpStoredHeatingTemperatures';

        const loadStoredHeatingTemperatures = () => {
            try {
                const raw = window.sessionStorage
                    ? window.sessionStorage.getItem(storedHeatingTemperatureKey)
                    : null;

                return raw ? JSON.parse(raw) : {};
            } catch (error) {
                return {};
            }
        };

        const saveStoredHeatingTemperatures = (values) => {
            try {
                if (window.sessionStorage) {
                    window.sessionStorage.setItem(
                        storedHeatingTemperatureKey,
                        JSON.stringify(values)
                    );
                }
            } catch (error) {
                // Anzeige funktioniert auch ohne Session-Speicher.
            }
        };

        let storedHeatingTemperatures = loadStoredHeatingTemperatures();

        const normalizeTemperatureUnits = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Einheitliche Darstellung sämtlicher numerischer Temperaturen:
             * immer "°C".
             *
             * Die Original-Card mischt je nach Element Werte mit "℃", "°C"
             * oder ganz ohne Einheit. Wir normalisieren deshalb ausschließlich
             * Text-Elemente mit Temp/Temperature im ID-Namen UND einem Zahlenwert.
             * Beschriftungen wie "Vorlauftemperatur" bleiben unangetastet.
             */
            svg.querySelectorAll(
                'text[id*="Temp"], text[id*="Temperature"]'
            ).forEach((element) => {
                const raw = String(element.textContent || '').trim();

                if (!/[+-]?\d+(?:[.,]\d+)?/.test(raw)) {
                    return;
                }

                const match = raw.match(/[+-]?\d+(?:[.,]\d+)?/);
                if (!match) {
                    return;
                }

                element.textContent = match[0] + ' °C';
            });
        };

        const applyThermalSolarVisualization = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const solarGroup = svg.querySelector('#gThermalSolar');

            if (!solarGroup) {
                return;
            }

            /*
             * Die Solarthermie-Gruppe wurde beim Einbetten der Original-SVG
             * zunächst auf display:none gesetzt. Wenn Solarthermie in Symcon
             * aktiviert ist, halten wir die gesamte Gruppe inklusive der
             * Solar-Wendel ausdrücklich sichtbar.
             */
            if (!currentConfig.thermalSolarAvailable) {
                solarGroup.style.setProperty('display', 'none', 'important');
                solarGroup.style.setProperty('visibility', 'hidden', 'important');

                const existingCoil =
                    svg.querySelector('#symconThermalSolarTankCoil');

                if (existingCoil) {
                    existingCoil.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    existingCoil.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                }

                const existingHotConnector =
                    svg.querySelector('#symconThermalSolarHotConnector');

                if (existingHotConnector) {
                    existingHotConnector.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    existingHotConnector.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                }

                const existingReturnText =
                    svg.querySelector('#symconThermalSolarReturnTemp');

                if (existingReturnText) {
                    existingReturnText.style.setProperty(
                        'display',
                        'none',
                        'important'
                    );
                    existingReturnText.style.setProperty(
                        'visibility',
                        'hidden',
                        'important'
                    );
                }

                return;
            }

            solarGroup.style.setProperty('display', 'inline', 'important');
            solarGroup.style.setProperty('visibility', 'visible', 'important');

            /*
             * Solarpumpe nur anzeigen, wenn dafür tatsächlich eine Variable
             * konfiguriert wurde. Die Solarthermie selbst kann also sichtbar sein,
             * ohne dass automatisch eine Pumpe dargestellt wird.
             */
            const solarPumpGroup = svg.querySelector('#gThermalSolarPump');
            if (solarPumpGroup) {
                const pumpConfigured = !!currentConfig.thermalSolarPump;

                solarPumpGroup.style.setProperty(
                    'display',
                    pumpConfigured ? 'inline' : 'none',
                    'important'
                );
                solarPumpGroup.style.setProperty(
                    'visibility',
                    pumpConfigured ? 'visible' : 'hidden',
                    'important'
                );
            }

            /*
             * Kühle Solar-Rücklauftemperatur nur anzeigen, wenn Solar Rücklauf
             * tatsächlich konfiguriert ist.
             */
            let solarReturnText =
                svg.querySelector('#symconThermalSolarReturnTemp');

            if (currentConfig.thermalSolarReturnTemp) {
                if (!solarReturnText) {
                    solarReturnText = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'text'
                    );
                    solarReturnText.setAttribute(
                        'id',
                        'symconThermalSolarReturnTemp'
                    );
                    /*
                     * Gleiche X-Position wie textThermalSolarFluxTemp (x=850),
                     * direkt darunter.
                     */
                    /*
                     * Rücklauftemperatur auf derselben horizontalen Linie/
                     * X-Ausrichtung wie die vorhandene Solar-Temperatur,
                     * aber direkt unterhalb der kühlen Rücklaufleitung.
                     */
                    solarReturnText.setAttribute('x', '850');
                    solarReturnText.setAttribute('y', '634');
                    solarReturnText.setAttribute('text-anchor', 'end');
                    solarReturnText.setAttribute('xml:space', 'preserve');
                    solarReturnText.style.setProperty(
                        'font-size',
                        '16px',
                        'important'
                    );
                    solarReturnText.style.setProperty(
                        'fill',
                        'var(--primary-text-color)',
                        'important'
                    );

                    solarGroup.appendChild(solarReturnText);
                }

                const returnValue =
                    readStateNumber(currentConfig.thermalSolarReturnTemp);

                if (returnValue !== null) {
                    solarReturnText.textContent =
                        new Intl.NumberFormat('de-CH', {
                            minimumFractionDigits: 1,
                            maximumFractionDigits: 1
                        }).format(returnValue) + ' °C';
                } else {
                    solarReturnText.textContent = '';
                }

                solarReturnText.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                solarReturnText.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );
            } else if (solarReturnText) {
                solarReturnText.textContent = '';
                solarReturnText.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
                solarReturnText.style.setProperty(
                    'visibility',
                    'hidden',
                    'important'
                );
            }

            /*
             * Die Original-SVG verwendet für die Solar-Wendel lediglich ein <use>
             * in gThermalSolar. Dieses liegt in der SVG-Reihenfolge HINTER der
             * später gezeichneten, gefüllten Boilerfläche und kann deshalb
             * vollständig verdeckt werden.
             *
             * Wir erzeugen daher einen eigenständigen Pfad direkt in gTankWW,
             * unmittelbar NACH dem Boiler-Chassis. So liegt die Solar-Wendel
             * sichtbar über der Speicherfüllung, aber weiterhin sauber innerhalb
             * des Boilers.
             */
            const tankGroup = svg.querySelector('#gTankWW');
            const tankChassis = svg.querySelector('#pathTankWWChassis');
            const sourceCoil = svg.querySelector('#pathPipeHotColdHeatpump');
            const originalSolarUse = svg.querySelector('#usePathPipeThermalSolarTank');

            let solarCoil = svg.querySelector('#symconThermalSolarTankCoil');

            if (!solarCoil && tankGroup && tankChassis && sourceCoil) {
                solarCoil = sourceCoil.cloneNode(false);
                solarCoil.setAttribute('id', 'symconThermalSolarTankCoil');
                solarCoil.setAttribute('transform', 'translate(193,90)');
                solarCoil.removeAttribute('style');

                if (tankChassis.nextSibling) {
                    tankGroup.insertBefore(solarCoil, tankChassis.nextSibling);
                } else {
                    tankGroup.appendChild(solarCoil);
                }
            }

            /*
             * Die ursprüngliche <use>-Kopie aus gThermalSolar unterdrücken,
             * damit bei halbtransparentem Original-Speicher keine doppelte
             * Wendel sichtbar wird.
             */
            if (originalSolarUse) {
                originalSolarUse.style.setProperty('display', 'none', 'important');
                originalSolarUse.style.setProperty('visibility', 'hidden', 'important');
            }

            if (solarCoil) {
                solarCoil.style.setProperty('display', 'inline', 'important');
                solarCoil.style.setProperty('visibility', 'visible', 'important');
                solarCoil.style.setProperty('fill', 'none', 'important');
                solarCoil.style.setProperty('stroke-width', '5', 'important');
                solarCoil.style.setProperty('stroke-opacity', '1', 'important');
            }

            /*
             * Kurzes heißes Verbindungsstück zwischen Solar-Vorlauf und
             * Solar-Wendel. In der Original-SVG kann dieser Bereich durch die
             * später gezeichnete Boilerfläche optisch unterbrochen werden.
             *
             * Deshalb als eigener Pfad direkt in gTankWW und damit oberhalb der
             * Speicherfüllung zeichnen.
             */
            let solarHotConnector =
                svg.querySelector('#symconThermalSolarHotConnector');

            if (!solarHotConnector && tankGroup) {
                solarHotConnector = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'path'
                );
                solarHotConnector.setAttribute(
                    'id',
                    'symconThermalSolarHotConnector'
                );

                /*
                 * Eintritt der Solar-Wendel liegt bei y=540.
                 * Das kurze Stück verbindet Boilerwand (x=745) mit dem
                 * vorhandenen Solar-Vorlauf/Wendelanschluss (x=791).
                 */
                solarHotConnector.setAttribute(
                    'd',
                    'M 745 540 H 791'
                );
                solarHotConnector.setAttribute('fill', 'none');

                tankGroup.appendChild(solarHotConnector);
            }

            if (solarHotConnector) {
                solarHotConnector.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'stroke-width',
                    '5',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'stroke-opacity',
                    '1',
                    'important'
                );
                solarHotConnector.style.setProperty(
                    'fill',
                    'none',
                    'important'
                );
            }

            /*
             * Bei abgeschalteten eigenen Temperaturfarben bleibt die originale
             * Farbgebung der Heat-Pump-Card bestehen.
             */
            if (!currentConfig.useCustomTemperatureColors) {
                [
                    '#pathPipeThermalSolarHotWater',
                    '#pathPipeThermalSolarColdWater',
                    '#rectThermalSolarPanel',
                    '#symconThermalSolarTankCoil'
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (!element) {
                        return;
                    }

                    element.style.removeProperty('stroke');
                    element.style.removeProperty('stroke-opacity');
                    element.style.removeProperty('fill');
                    element.style.removeProperty('fill-opacity');
                });

                if (solarCoil) {
                    solarCoil.style.setProperty(
                        'stroke',
                        'url(#linearGradientPipe1)',
                        'important'
                    );
                    solarCoil.style.setProperty(
                        'stroke-opacity',
                        '1',
                        'important'
                    );
                }

                if (solarHotConnector) {
                    solarHotConnector.style.setProperty(
                        'stroke',
                        '#ff0000',
                        'important'
                    );
                }

                return;
            }

            // Kollektortemperatur bleibt weiterhin als Messwert der Card erhalten.
            // Für die Farbrichtung des Panels sind aber Solar-Vorlauf und
            // Solar-Rücklauf maßgebend.
            const panelTemperature =
                readStateNumber(currentConfig.thermalSolarPanelTemp);

            const supplyTemperature =
                readStateNumber(currentConfig.thermalSolarFluxTemp);

            /*
             * Solar-Rücklauf ist neu optional konfigurierbar.
             * Fehlt er, verwenden wir die untere Boilertemperatur als sinnvolle
             * Näherung für das Fluid, das die Solar-Wendel wieder verlässt.
             */
            const returnTemperature =
                readStateNumber(currentConfig.thermalSolarReturnTemp)
                ?? readStateNumber(currentConfig.tankTempWWDown)
                ?? readStateNumber(currentConfig.tankTempWWMiddle)
                ?? readStateNumber(currentConfig.tankTempWWUp);

            void panelTemperature;

            const supplyColor = temperatureColor(supplyTemperature);
            const returnColor = temperatureColor(returnTemperature);

            const setStroke = (selector, color) => {
                const element = svg.querySelector(selector);
                if (!element || !color) {
                    return;
                }

                element.style.setProperty('stroke', color, 'important');
                element.style.setProperty('stroke-opacity', '1', 'important');
            };

            const setFill = (selector, color) => {
                const element = svg.querySelector(selector);
                if (!element || !color) {
                    return;
                }

                element.style.setProperty('fill', color, 'important');
                element.style.setProperty('fill-opacity', '1', 'important');
            };

            /*
             * Solarleitungen:
             * - pathPipeThermalSolarHotWater ist der heiße Vorlauf vom Panel
             *   zum Boiler.
             * - pathPipeThermalSolarColdWater ist der kühlere Rücklauf vom
             *   Boiler zurück zum Panel.
             */
            setStroke('#pathPipeThermalSolarHotWater', supplyColor);
            setStroke('#pathPipeThermalSolarColdWater', returnColor);
            setStroke('#symconThermalSolarHotConnector', supplyColor);

            /*
             * Solarpanel:
             * Die Originalgeometrie läuft von links (heißer Ausgang/Vorlauf)
             * nach rechts (kühler Eingang/Rücklauf).
             *
             * Daher muss der Panelverlauf zwingend sein:
             *   links  = Solar-Vorlauffarbe
             *   rechts = Solar-Rücklauffarbe
             *
             * Die Kollektortemperatur bleibt ein eigener Messwert und wird NICHT
             * mehr als Endfarbe des Panels verwendet.
             */
            let panelGradient = svg.querySelector(
                '#symconLinearGradientThermalSolarPanel'
            );

            if (!panelGradient) {
                const originalPanelGradient =
                    svg.querySelector('#linearGradient3');

                if (originalPanelGradient) {
                    panelGradient = originalPanelGradient.cloneNode(true);
                    panelGradient.setAttribute(
                        'id',
                        'symconLinearGradientThermalSolarPanel'
                    );

                    panelGradient.querySelectorAll('stop').forEach(
                        (stop, index) => {
                            stop.setAttribute(
                                'id',
                                'symconThermalSolarPanelStop' + (index + 1)
                            );
                        }
                    );

                    originalPanelGradient.parentNode.appendChild(panelGradient);
                }
            }

            if (panelGradient && supplyColor && returnColor) {
                const panelStops = panelGradient.querySelectorAll('stop');

                if (panelStops.length >= 2) {
                    panelStops[0].style.setProperty(
                        'stop-color',
                        supplyColor,
                        'important'
                    );
                    panelStops[0].setAttribute(
                        'stop-color',
                        supplyColor
                    );

                    panelStops[panelStops.length - 1].style.setProperty(
                        'stop-color',
                        returnColor,
                        'important'
                    );
                    panelStops[panelStops.length - 1].setAttribute(
                        'stop-color',
                        returnColor
                    );
                }

                const panel = svg.querySelector('#rectThermalSolarPanel');

                if (panel) {
                    panel.style.setProperty(
                        'fill',
                        'url(#symconLinearGradientThermalSolarPanel)',
                        'important'
                    );
                    panel.style.setProperty(
                        'fill-opacity',
                        '1',
                        'important'
                    );
                }
            }

            /*
             * Eigener Gradient nur für den eigenständigen Solar-Wendel-Pfad.
             * Damit beeinflusst Solar weder die Wärmepumpen- noch die normale
             * Boiler-Wendel.
             */
            let gradient = svg.querySelector('#symconLinearGradientThermalSolar');

            if (!gradient) {
                const sourceGradient = svg.querySelector('#linearGradientPipe1');

                if (sourceGradient) {
                    gradient = sourceGradient.cloneNode(true);
                    gradient.setAttribute(
                        'id',
                        'symconLinearGradientThermalSolar'
                    );

                    gradient.querySelectorAll('stop').forEach((stop, index) => {
                        stop.setAttribute(
                            'id',
                            'symconThermalSolarStop' + (index + 1)
                        );
                    });

                    sourceGradient.parentNode.appendChild(gradient);
                }
            }

            if (gradient && supplyColor && returnColor) {
                const stops = gradient.querySelectorAll('stop');

                if (stops.length >= 2) {
                    stops[0].style.setProperty(
                        'stop-color',
                        supplyColor,
                        'important'
                    );
                    stops[0].setAttribute('stop-color', supplyColor);

                    stops[stops.length - 1].style.setProperty(
                        'stop-color',
                        returnColor,
                        'important'
                    );
                    stops[stops.length - 1].setAttribute(
                        'stop-color',
                        returnColor
                    );
                }

                if (solarCoil) {
                    solarCoil.style.setProperty(
                        'stroke',
                        'url(#symconLinearGradientThermalSolar)',
                        'important'
                    );
                    solarCoil.style.setProperty(
                        'stroke-opacity',
                        '1',
                        'important'
                    );
                }
            }
        };

        const applySingleCircuitTemperatureDisplay = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;

            /*
             * Diese Erweiterung gilt bewusst nur bei genau EINEM Heizkreis.
             */
            const circuits = [
                {
                    key: '1',
                    type: String(currentConfig.heatingCircuitType1 || 'off'),
                    supply: currentConfig.supplyTemperatureHeating,
                    reflux: currentConfig.refluxTemperatureHeating,
                    supplyText: '#textSupplyTemperatureHeating',
                    refluxText: '#textRefluxTemperatureHeating'
                },
                {
                    key: '2',
                    type: String(currentConfig.heatingCircuitType2 || 'off'),
                    supply: currentConfig.supplyTemperatureHeating2,
                    reflux: currentConfig.refluxTemperatureHeating2,
                    supplyText: '#textSupplyTemperatureHeating2',
                    refluxText: '#textRefluxTemperatureHeating2'
                },
                {
                    key: '3',
                    type: String(currentConfig.heatingCircuitType3 || 'off'),
                    supply: currentConfig.supplyTemperatureHeating3,
                    reflux: currentConfig.refluxTemperatureHeating3,
                    supplyText: '#textSupplyTemperatureHeating3',
                    refluxText: '#textRefluxTemperatureHeating3'
                }
            ];

            const activeCircuits = circuits.filter(
                (circuit) => circuit.type !== 'off'
            );

            const tankGroup = svg.querySelector('#gTankWW');

            /*
             * Zusatztexte am Boiler entfernen, wenn mehr oder weniger als
             * ein Heizkreis aktiv ist.
             */
            const removeBoilerTexts = () => {
                [
                    '#symconBoilerSupplyTemperature',
                    '#symconBoilerRefluxTemperature'
                ].forEach((selector) => {
                    const element = svg.querySelector(selector);
                    if (element && element.parentNode) {
                        element.parentNode.removeChild(element);
                    }
                });
            };

            if (activeCircuits.length !== 1 || !tankGroup) {
                removeBoilerTexts();
                return;
            }

            const circuit = activeCircuits[0];

            /*
             * Allein die Stellung des konfigurierten Umschaltventils ist
             * maßgebend:
             *   aktiv   = Boiler
             *   inaktiv = Heizung
             *
             * Ohne Ventil gibt es keine Einfrier-/Umschaltlogik.
             */
            const valveConfigured = !!currentConfig.wwHeatingValve;
            const boilerActive =
                valveConfigured && stateIsOn(currentConfig.wwHeatingValve);

            const supplyTemperature = readStateNumber(circuit.supply);
            const refluxTemperature = readStateNumber(circuit.reflux);

            const formatTemperature = (value) => {
                if (value === null || !Number.isFinite(Number(value))) {
                    return '';
                }

                return new Intl.NumberFormat('de-CH', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1
                }).format(Number(value)) + ' °C';
            };

            /*
             * Im Heizbetrieb die letzten echten Heizkreiswerte speichern.
             * Beim Boilerbetrieb werden diese Werte nicht mehr überschrieben.
             */
            if (
                !boilerActive
                && supplyTemperature !== null
                && refluxTemperature !== null
            ) {
                storedHeatingTemperatures[circuit.key] = {
                    supply: supplyTemperature,
                    reflux: refluxTemperature
                };

                saveStoredHeatingTemperatures(storedHeatingTemperatures);
            }

            /*
             * Beim Umschalten auf Boiler bleiben die Temperaturtexte am
             * Heizkörper / an der Fußbodenheizung auf den letzten Heizwerten.
             */
            if (boilerActive) {
                const stored = storedHeatingTemperatures[circuit.key];

                if (stored) {
                    const supplyText = svg.querySelector(circuit.supplyText);
                    const refluxText = svg.querySelector(circuit.refluxText);

                    if (supplyText) {
                        supplyText.textContent =
                            formatTemperature(stored.supply);
                    }

                    if (refluxText) {
                        refluxText.textContent =
                            formatTemperature(stored.reflux);
                    }
                }
            }

            const boilerTemperature =
                readStateNumber(currentConfig.tankTempWWUp)
                ?? readStateNumber(currentConfig.tankTempWWMiddle)
                ?? readStateNumber(currentConfig.tankTempWWDown);

            /*
             * Zwei zusätzliche Temperaturwerte neben der Boiler-Wendel.
             */
            const ensureText = (id, y) => {
                let element = svg.querySelector('#' + id);

                if (!element) {
                    element = document.createElementNS(
                        'http://www.w3.org/2000/svg',
                        'text'
                    );
                    element.setAttribute('id', id);
                    element.setAttribute('x', '590');
                    element.setAttribute('y', String(y));
                    element.setAttribute('text-anchor', 'end');
                    element.setAttribute('xml:space', 'preserve');
                    element.style.setProperty('font-size', '14px', 'important');
                    element.style.setProperty(
                        'fill',
                        'var(--primary-text-color)',
                        'important'
                    );

                    tankGroup.appendChild(element);
                }

                return element;
            };

            const boilerSupplyText = ensureText(
                'symconBoilerSupplyTemperature',
                438
            );
            const boilerRefluxText = ensureText(
                'symconBoilerRefluxTemperature',
                548
            );

            if (boilerActive) {
                /*
                 * Boiler wird geladen:
                 * Die beiden Zusatzwerte zeigen ausschließlich während dieser
                 * Phase die aktuell anliegenden Vorlauf-/Rücklauftemperaturen.
                 *
                 * Gleichzeitig bleiben die Temperaturwerte am einzigen Heizkreis
                 * auf den zuletzt im Heizbetrieb gespeicherten Werten eingefroren.
                 * Maßgebend ist ausschließlich die Stellung des
                 * Umschaltventils Warmwasser/Heizung.
                 */
                boilerSupplyText.textContent =
                    formatTemperature(supplyTemperature);
                boilerRefluxText.textContent =
                    formatTemperature(refluxTemperature);

                boilerSupplyText.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
                boilerRefluxText.style.setProperty(
                    'display',
                    'inline',
                    'important'
                );
            } else {
                /*
                 * Keine Boilerladung:
                 * Die zusätzlichen Vorlauf-/Rücklaufwerte am Boiler vollständig
                 * ausblenden. Die normale Boilertemperatur im Speicher bleibt
                 * selbstverständlich erhalten.
                 */
                boilerSupplyText.textContent = '';
                boilerRefluxText.textContent = '';

                boilerSupplyText.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
                boilerRefluxText.style.setProperty(
                    'display',
                    'none',
                    'important'
                );
            }

            /*
             * Ohne konfiguriertes Ventil gibt es keine eindeutige Boilerphase.
             * Deshalb werden die zusätzlichen Werte ebenfalls nicht angezeigt.
             */
            if (!valveConfigured) {
                removeBoilerTexts();
            }
        };

        const applyTerminology = (card) => {
            if (!card || !card.content) {
                return;
            }

            const condenserText = card.content.querySelector('#textCondenser');
            if (condenserText) {
                condenserText.textContent = 'Kondensator';
            }
        };

        const applyOptionalStatusVisibility = (card) => {
            if (!card || !card.content) {
                return;
            }

            const setConfiguredVisibility = (selector, configured) => {
                const element = card.content.querySelector(selector);
                if (!element) {
                    return;
                }

                if (!configured) {
                    element.style.setProperty('display', 'none', 'important');
                    element.style.setProperty('visibility', 'hidden', 'important');
                } else {
                    element.style.removeProperty('visibility');
                    element.style.removeProperty('display');
                }
            };

            // Wärmepumpe Ein/Aus:
            // Ohne Datenpunkt kein "Aus"-Symbol anzeigen.
            setConfiguredVisibility(
                '#gHPStatusOff',
                !!currentConfig.heatingPumpStatusOnOff
            );

            // Tag/Nacht:
            // Die Original-Card zeigt ohne Variable automatisch die Sonne.
            // In Symcon werden Sonne UND Mond nur angezeigt, wenn eine
            // Nachtbetriebsvariable tatsächlich konfiguriert ist.
            const nightModeConfigured = !!currentConfig.heatingPumpNightMode;
            if (!nightModeConfigured) {
                setConfiguredVisibility('#gTimeSymbolDay', false);
                setConfiguredVisibility('#gTimeSymbolNight', false);
            }

            // Warmwasser-/Heizungs-Umschaltventil:
            // Die Original-Card zeichnet es in Grundstellung auch ohne Datenpunkt.
            // Ohne Zuordnung soll das Ventilsymbol vollständig verschwinden.
            if (!currentConfig.wwHeatingValve) {
                setConfiguredVisibility('#gWWHeatingValve', false);
            }
        };


        const applyThreeHeaterRods = (card) => {
            if (!card || !card.content) {
                return;
            }

            const svg = card.content;
            const tankGroup = svg.querySelector('#gTankWW');
            const original = svg.querySelector('#pathHeaterRodWW');

            if (!tankGroup || !original) {
                return;
            }

            const heaterRodCount = Math.max(
                0,
                Math.min(3, Number(currentConfig.heaterRodCount || 0))
            );

            /*
             * Der einzelne Original-WW-Heizstab wird grundsätzlich unterdrückt.
             * Sonst würde er bei Anzahl 0 durch heaterRodWW wieder erscheinen.
             */
            original.style.setProperty('display', 'none', 'important');
            original.style.setProperty('visibility', 'hidden', 'important');

            const ensureRod = (id, y) => {
                let rod = svg.querySelector('#' + id);

                if (!rod) {
                    rod = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    rod.setAttribute('id', id);
                    rod.setAttribute('fill', 'none');
                    tankGroup.appendChild(rod);
                }

                /*
                 * Position bei jeder Aktualisierung neu setzen. So rutschen die
                 * Heizstäbe beim Ändern der Anzahl sofort von unten nach oben.
                 */
                /*
                 * Heizstäbe an der LINKEN Boilerwand:
                 * Eintritt/Austritt liegen bei x=655 und die Schleife ragt
                 * nach rechts in den Speicher. Dadurch bleibt die rechte Seite
                 * für Solar-/Boiler-Wendel und Temperaturtexte frei.
                 */
                rod.setAttribute(
                    'd',
                    'M 655 ' + y + ' H 695 C 701 ' + y + ' 701 ' + (y - 10)
                    + ' 695 ' + (y - 10) + ' H 655'
                );

                rod.style.setProperty('fill', 'none', 'important');
                rod.style.setProperty('stroke-width', '5', 'important');
                rod.style.setProperty('stroke-linecap', 'round', 'important');
                rod.style.setProperty('stroke-linejoin', 'round', 'important');

                return rod;
            };

            /*
             * Die drei Positionen liegen gut verteilt im WW-Speicher.
             * Inaktiv bleibt der vorhandene Stab sichtbar, aber dezent.
             * Aktiv erhält jede Stufe eine klar erkennbare warme Farbe.
             */
            /*
             * Die vorhandenen Heizstäbe werden immer von unten nach oben angeordnet:
             * 1 Heizstab  -> 625 (ganz unten)
             * 2 Heizstäbe -> 595 / 625
             * 3 Heizstäbe -> 565 / 595 / 625
             */
            const rodPositions = {
                1: [610],
                2: [580, 610],
                3: [550, 580, 610]
            };

            const configuredPositions = rodPositions[heaterRodCount] || [];
            const rodEntities = [
                currentConfig.heaterRod1,
                currentConfig.heaterRod2,
                currentConfig.heaterRod3
            ];
            const rodThresholds = [
                currentConfig.heaterRod1Threshold,
                currentConfig.heaterRod2Threshold,
                currentConfig.heaterRod3Threshold
            ];
            const activeRodColor =
                temperatureColor(60) || '#f04444';
            const rodColors = [activeRodColor, activeRodColor, activeRodColor];

            const rods = [0, 1, 2].map((index) => ({
                element: ensureRod(
                    'pathHeaterRodWW' + (index + 1),
                    configuredPositions[index] || 610
                ),
                entity: rodEntities[index],
                threshold: rodThresholds[index],
                activeColor: rodColors[index]
            }));

            const isActive = (entity, threshold) => {
                if (!entity || !currentStates || !currentStates[entity]) {
                    return false;
                }

                const raw = currentStates[entity].state;
                const value = String(raw ?? '').trim().toLowerCase();
                const limit = Number.isFinite(Number(threshold)) ? Number(threshold) : 1;

                // Bool: true entspricht 1, false entspricht 0.
                if (['true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(value)) {
                    return 1 >= limit;
                }

                if (['false', 'off', 'no', 'nein', 'aus', 'inactive', 'inaktiv', ''].includes(value)) {
                    return 0 >= limit;
                }

                const numeric = Number(raw);
                return Number.isFinite(numeric) && numeric >= limit;
            };

            rods.forEach((rodInfo, index) => {
                const exists = index < heaterRodCount;

                if (!exists) {
                    rodInfo.element.style.setProperty('display', 'none', 'important');
                    rodInfo.element.style.setProperty('visibility', 'hidden', 'important');
                    return;
                }

                const active = isActive(rodInfo.entity, rodInfo.threshold);

                rodInfo.element.style.setProperty('display', 'block', 'important');
                rodInfo.element.style.setProperty('visibility', 'visible', 'important');
                rodInfo.element.style.setProperty(
                    'stroke',
                    active ? rodInfo.activeColor : 'rgba(255,255,255,0.55)',
                    'important'
                );

                /*
                 * Kontur erhöht den Kontrast auch bei rotem/orangem Speicher.
                 */
                rodInfo.element.style.setProperty(
                    'filter',
                    'drop-shadow(0 0 1.5px rgba(0,0,0,0.95))',
                    'important'
                );
            });
        };

        const applyControlIcons = (card) => {
            if (!card || !card.content) {
                return;
            }

            const enabledColor = resolveLayoutTextColor();

            const definitions = [
                {
                    functionName: 'heating',
                    selector: '#gHPStatusHeating',
                    entity: currentConfig.heatingPumpHeatingMode,
                    runningColor: '#ff9500'
                },
                {
                    functionName: 'hotwater',
                    selector: '#gHPStatusWW',
                    entity: currentConfig.heatingPumpHotWaterMode,
                    runningColor: '#ff9500'
                },
                {
                    functionName: 'cooling',
                    selector: '#gHPStatusCooling',
                    entity: currentConfig.heatingPumpCoolingMode,
                    runningColor: '#0a84ff'
                }
            ];

            definitions.forEach((definition) => {
                const group = card.content.querySelector(definition.selector);
                if (!group) {
                    console.warn('Statussymbol nicht gefunden:', definition.selector);
                    return;
                }

                const control = currentControls && currentControls[definition.functionName];

                // Läuft gerade wirklich = zentrale Ist-Statusvariable.
                const running = stateIsOn(definition.entity);

                const hasControl = !!(
                    control
                    && control.configured
                    && Array.isArray(control.options)
                    && control.options.length > 0
                );

                const visible =
                    hasControl
                    || (currentControls && currentControls.hasOperatingStatus)
                    || running;

                group.style.setProperty('display', visible ? 'inline' : 'none', 'important');

                if (!visible) {
                    return;
                }

                group.style.setProperty('visibility', 'visible', 'important');
                group.style.removeProperty('filter');
                group.style.setProperty('opacity', '1', 'important');

                if (hasControl && !control.enabled) {
                    // Nicht aktiviert / Steuerung = AUS.
                    setIconColor(group, '#777777');
                    group.style.setProperty('opacity', '0.60', 'important');
                } else if (running) {
                    // Funktion läuft gerade tatsächlich.
                    setIconColor(group, definition.runningColor);
                    group.style.setProperty(
                        'filter',
                        'brightness(1.15) saturate(1.15)',
                        'important'
                    );
                } else {
                    // Aktiviert/freigegeben, aber momentan nicht laufend.
                    setIconColor(group, enabledColor);
                }

                if (hasControl) {
                    group.style.setProperty('cursor', 'pointer', 'important');
                    group.style.setProperty('pointer-events', 'all', 'important');

                    if (!group.dataset.symconControlBound) {
                        group.dataset.symconControlBound = '1';
                        group.addEventListener(
                            'click',
                            (event) => openModeMenu(definition.functionName, event)
                        );
                    }
                } else {
                    group.style.setProperty('cursor', 'default', 'important');
                }
            });
        };

        const applyFanAnimation = (card) => {
            if (!card || !card.content || currentConfig.heatingPumpType !== 'A2W') {
                return;
            }

            const fan = card.content.querySelector('#pathHPFan');
            if (!fan) {
                return;
            }

            const fanEntity = currentConfig.fanSpeed;

            if (!fanEntity || !currentStates[fanEntity]) {
                fan.classList.remove('rotate');
                fan.style.animationDuration = '';
                return;
            }

            const rawState = currentStates[fanEntity].state;
            const normalized = String(rawState ?? '').trim().toLowerCase();

            let rpm = 0;

            /*
             * Bool-Unterstützung:
             * true/on/ein/aktiv = mittlere Lüfterleistung, entspricht ca. 250 U/min.
             * false/off/aus     = Lüfter steht.
             */
            if (['true', 'on', 'yes', 'ja', 'ein', 'active', 'aktiv'].includes(normalized)) {
                rpm = 250;
            } else if (['false', 'off', 'no', 'nein', 'aus', 'inactive', 'inaktiv', ''].includes(normalized)) {
                rpm = 0;
            } else {
                rpm = Number(rawState);

                if (Number.isNaN(rpm)) {
                    rpm = 0;
                }
            }

            if (rpm <= 0) {
                fan.classList.remove('rotate');
                fan.style.animationDuration = '';
                return;
            }

            /*
             * Optische Kennlinie für reale 0–500 U/min.
             *
             * Ziel:
             *   ca. 100 U/min -> 2.5 s/Umdrehung
             *   ca. 200 U/min -> 1.8 s/Umdrehung
             *   ca. 300 U/min -> 1.2 s/Umdrehung
             *   ca. 400 U/min -> 0.8 s/Umdrehung
             *   ca. 500 U/min -> 0.5 s/Umdrehung
             *
             * Zwischenwerte werden stufenlos interpoliert.
             * Werte über 500 U/min werden auf die schnellste Darstellung begrenzt.
             */
            const clampedRpm = Math.max(0, Math.min(500, rpm));

            const points = [
                [0,   3.0],
                [100, 2.5],
                [200, 1.8],
                [300, 1.2],
                [400, 0.8],
                [500, 0.5]
            ];

            let duration = 3.0;

            for (let i = 0; i < points.length - 1; i++) {
                const [rpm1, duration1] = points[i];
                const [rpm2, duration2] = points[i + 1];

                if (clampedRpm >= rpm1 && clampedRpm <= rpm2) {
                    const factor = (clampedRpm - rpm1) / (rpm2 - rpm1);
                    duration = duration1 + (duration2 - duration1) * factor;
                    break;
                }
            }

            fan.classList.add('rotate');
            fan.style.animationDuration = duration.toFixed(2) + 's';
        };

        document.addEventListener('click', (event) => {
            const menu = document.getElementById('wp-mode-menu');
            if (menu && menu.style.display === 'block' && !menu.contains(event.target)) {
                closeModeMenu();
            }
        });

        const applyCardData = () => {
            const card = document.getElementById('wp-card');

            if (!card) {
                showError('Das Element <heat-pump-card> wurde im HTML nicht gefunden.');
                return;
            }

            try {
                clearError();

                /*
                 * Alle Symcon-Anpassungen synchron direkt NACH setValues() der
                 * Original-Card ausführen. Keine Timer und kein Polling.
                 */
                if (!card.__symconSetValuesHookInstalled && typeof card.setValues === 'function') {
                    const originalSetValues = card.setValues;

                    card.setValues = function(hass) {
                        const result = originalSetValues.call(this, hass);

                        if (this.content) {
                            applyCoolingVisualization(this);
                            updateRefrigerantValues(this);
                            applyTerminology(this);
                            applyThemeColors(this);
                            applyRefrigerantCircuitMode(this);
                            applyOptionalStatusVisibility(this);
                            applyWWValvePipeGeometry(this);
                            restoreOriginalTemperatureColors(this);
                            applyHeatingCircuitTemperatureColors(this);
                            applyTemperatureColorOpacity(this);
                            applyThreeHeaterRods(this);
                            applyControlIcons(this);
                            applyFanAnimation(this);
                            applyHeatingReturnContinuity(this);
                            applySingleCircuitTemperatureDisplay(this);
                            applyThermalSolarVisualization(this);
                            normalizeTemperatureUnits(this);
                        }

                        return result;
                    };

                    card.__symconSetValuesHookInstalled = true;
                }

                card.setConfig(currentConfig);
                card.hass = {
                    language: 'de-DE',
                    states: currentStates
                };

                /*
                 * Falls die Card beim Setzen von hass keinen setValues()-Aufruf
                 * auslöst, einmal synchron mit dem aktuellen Zustand anwenden.
                 */
                if (card.content) {
                    applyCoolingVisualization(card);
                    updateRefrigerantValues(card);
                    applyTerminology(card);
                    applyThemeColors(card);
                    applyRefrigerantCircuitMode(card);
                    applyOptionalStatusVisibility(card);
                    applyWWValvePipeGeometry(card);
                    restoreOriginalTemperatureColors(card);
                    applyHeatingCircuitTemperatureColors(card);
                    applyTemperatureColorOpacity(card);
                    applyThreeHeaterRods(card);
                    applyControlIcons(card);
                    applyFanAnimation(card);
                    applyHeatingReturnContinuity(card);
                    applySingleCircuitTemperatureDisplay(card);
                    applyThermalSolarVisualization(card);
                    normalizeTemperatureUnits(card);
                }
            } catch (error) {
                showError(
                    'Fehler beim Übergeben der Symcon-Daten an die Card:\\n'
                    + (error && error.stack ? error.stack : String(error))
                );
            }
        };

        /*
         * HTML-SDK: Nachrichten vom PHP-Modul.
         * GetVisualizationTile() liefert den kompletten Initialzustand.
         * Danach aktualisiert UpdateVisualizationValue() nur noch die Daten.
         */
        window.handleMessage = (message) => {
            let data = message;

            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch (error) {
                    showError('Ungültige HTML-SDK-Nachricht: ' + String(error));
                    return;
                }
            }

            if (!data || typeof data !== 'object') {
                return;
            }

            if (data.command === 'reloadHtml') {
                window.location.reload();
                return;
            }

            if (data.config) {
                currentConfig = data.config;
            }

            if (data.data) {
                currentData = data.data;
                currentStates = toCompatibilityStates(currentData);
            }

            if (data.controls) {
                currentControls = data.controls;
            }

            if (data.type === 'rebuild') {
                rebuildCard();
                return;
            }

            applyCardData();
        };

        if (window.matchMedia) {
            const scheme = window.matchMedia('(prefers-color-scheme: dark)');
            const onSchemeChange = () => {
                rebuildCard();
            };

            if (typeof scheme.addEventListener === 'function') {
                scheme.addEventListener('change', onSchemeChange);
            } else if (typeof scheme.addListener === 'function') {
                scheme.addListener(onSchemeChange);
            }
        }

        applyCardData();
  }
};
