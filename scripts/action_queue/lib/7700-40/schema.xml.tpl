<?xml version="1.0" encoding="UTF-8" ?>
<schema name="source" version="1.1">
    <types>
        <fieldType name="string" class="solr.StrField" sortMissingLast="true" omitNorms="true"/>
        <fieldType name="boolean" class="solr.BoolField" sortMissingLast="true" omitNorms="true"/>

        <fieldType name="int" class="solr.TrieIntField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
        <fieldType name="float" class="solr.TrieFloatField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
        <fieldType name="long" class="solr.TrieLongField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>
        <fieldType name="double" class="solr.TrieDoubleField" precisionStep="0" omitNorms="true" positionIncrementGap="0"/>

        <fieldType name="tint" class="solr.TrieIntField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
        <fieldType name="tfloat" class="solr.TrieFloatField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
        <fieldType name="tlong" class="solr.TrieLongField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>
        <fieldType name="tdouble" class="solr.TrieDoubleField" precisionStep="8" omitNorms="true" positionIncrementGap="0"/>

        <fieldType name="sint" class="solr.SortableIntField" sortMissingLast="true" omitNorms="true"/>
        <fieldType name="slong" class="solr.SortableLongField" sortMissingLast="true" omitNorms="true"/>
        <fieldType name="sfloat" class="solr.SortableFloatField" sortMissingLast="true" omitNorms="true"/>
        <fieldType name="sdouble" class="solr.SortableDoubleField" sortMissingLast="true" omitNorms="true"/>

	<fieldType name="date" class="solr.TrieDateField" omitNorms="true" precisionStep="0" positionIncrementGap="0"/>
	<fieldType name="tdate" class="solr.TrieDateField" omitNorms="true" precisionStep="6" positionIncrementGap="0"/>

        <fieldType name="text" class="solr.TextField">
            <analyzer type="index">
                <tokenizer class="solr.ChineseTokenizerFactory"/>
                <filter class="solr.StandardFilterFactory"/>
                <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1"
                        catenateWords="0" catenateNumbers="0" catenateAll="0" splitOnCaseChange="1"/>
                <filter class="solr.RemoveDuplicatesTokenFilterFactory"/>
            </analyzer>
            <analyzer type="query">
                <tokenizer class="solr.ChineseTokenizerFactory"/>
                <filter class="solr.StandardFilterFactory"/>
                <filter class="solr.WordDelimiterFilterFactory" generateWordParts="1" generateNumberParts="1"
                        catenateWords="0" catenateNumbers="0" catenateAll="0" splitOnCaseChange="1"/>
                <filter class="solr.RemoveDuplicatesTokenFilterFactory"/>
                <filter class="solr.SynonymFilterFactory" synonyms="synonyms.txt" ignoreCase="true" expand="true"/>
            </analyzer>
        </fieldType>

        <fieldtype name="ignored" stored="false" indexed="false" class="solr.StrField"/>
    </types>

    <fields>
        ${fields}
        <field name="_version_" type="long" indexed="true" stored="true"/>
    </fields>

    <uniqueKey>${uniqueKey}</uniqueKey>
    <defaultSearchField>${defaultSearchField}</defaultSearchField>
    <solrQueryParser defaultOperator="${defaultOperator}"/>
</schema>
