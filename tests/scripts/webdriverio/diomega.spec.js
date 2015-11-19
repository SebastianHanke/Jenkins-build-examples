"use strict"
var assert = require('assert');

describe('checkboxes', function() {

    it('checkbox 2 should be enabled', function*() {
        yield browser.url('/checkboxes');
        yield browser.isSelected('#checkboxes input:last-Child').then(function(isSelected) {
            assert.equal(isSelected, true);
            /*expect(isSelected).toBe(true);*/
        });
    });

    it('checkbox 1 should be enabled after clicking on it', function*() {
        yield browser.url('/checkboxes');
        yield browser.isSelected('#checkboxes input:first-Child').then(function(isSelected) {
            assert.equal(isSelected, false);
            /*expect(isSelected).toBe(false);*/
        });
        yield browser.click('#checkboxes input:first-Child');
        yield browser.isSelected('#checkboxes input:first-Child').then(function(isSelected) {
            assert.equal(isSelected, true);
            /*expect(isSelected).toBe(true);*/
        });
    });

});
