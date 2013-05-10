<?xml version="1.0" encoding="UTF-8" ?>
<config>
    <abortOnConfigurationError>${solr.abortOnConfigurationError:true}</abortOnConfigurationError>

    <!-- <dataDir>./data</dataDir> -->
    <lib dir="../../lib/7700-35/dist/" regex="apache-solr-dataimporthandler-\d.*\.jar" />
    <lib dir="../../lib/7700-35/contrib/dataimporthandler/lib/" regex=".*\.jar" />

    <indexDefaults>
        <useCompoundFile>false</useCompoundFile>
        <mergeFactor>10</mergeFactor>
        <maxBufferedDocs>1000</maxBufferedDocs>
        <maxMergeDocs>2147483647</maxMergeDocs>
        <maxFieldLength>10000</maxFieldLength>
        <writeLockTimeout>1000</writeLockTimeout>
        <commitLockTimeout>10000</commitLockTimeout>
        <lockType>native</lockType>
    </indexDefaults>

    <mainIndex>
        <useCompoundFile>false</useCompoundFile>
        <mergeFactor>10</mergeFactor>
        <unlockOnStartup>true</unlockOnStartup>
        <reopenReaders>true</reopenReaders>
        <termIndexInterval>256</termIndexInterval>
        <deletionPolicy class="solr.SolrDeletionPolicy">
            <str name="maxCommitsToKeep">1</str>
            <str name="maxOptimizedCommitsToKeep">0</str>
        </deletionPolicy>
        <infoStream file="INFOSTREAM.txt">false</infoStream>
    </mainIndex>

    <requestHandler name="/dataimport" class="org.apache.solr.handler.dataimport.DataImportHandler">
     <lst name="defaults">
         <str name="config">${dataimport-config}</str>
     </lst>
   </requestHandler>

    <updateHandler class="solr.DirectUpdateHandler2">
        <autoCommit>
            <maxDocs>${maxDocs}</maxDocs>
            <maxTime>${maxTime}</maxTime>
        </autoCommit>
    </updateHandler>

    <query>
        <maxBooleanClauses>1024</maxBooleanClauses>
        <filterCache
            class="solr.FastLRUCache"
            size="8192"
            initialSize="512"
            autowarmCount="256"/>
        <fieldValueCache
            class="solr.FastLRUCache"
            size="8192"
            autowarmCount="128"
            showItems="32"
        />
        <queryResultCache
            class="solr.LRUCache"
            size="8912"
            initialSize="512"
            autowarmCount="256"/>
        <documentCache
            class="solr.LRUCache"
            size="8192"
            initialSize="512"
            autowarmCount="0"/>
        <enableLazyFieldLoading>true</enableLazyFieldLoading>
        <queryResultWindowSize>20</queryResultWindowSize>
        <queryResultMaxDocsCached>200</queryResultMaxDocsCached>
        <useColdSearcher>true</useColdSearcher>
        <maxWarmingSearchers>4</maxWarmingSearchers>
    </query>

  <requestDispatcher handleSelect="true" >
    <requestParsers enableRemoteStreaming="true" multipartUploadLimitInKB="2048000" />
  </requestDispatcher>

  <requestHandler name="standard" class="solr.StandardRequestHandler">
     <lst name="defaults">
       <str name="echoParams">explicit</str>
     </lst>
  </requestHandler>

  <requestHandler name="dismax" class="solr.DisMaxRequestHandler">
        <lst name="defaults">
            <str name="echoParams">explicit</str>
            <str name="qf">
                title^10 community_name^20 address^15
            </str>
            <int name="qs">0</int>
            <str name="hl">true</str>
            <str name="hl.fl">title community_name address</str>
            <str name="hl.formatter">html</str>
            <str name="hl.fragmenter">gap</str>
        </lst>
    </requestHandler>
  <requestHandler name="/update" class="solr.XmlUpdateRequestHandler" />
  <requestHandler name="/admin/" class="solr.admin.AdminHandlers" />
  <requestHandler name="/admin/ping" class="PingRequestHandler">
    <lst name="defaults">
      <str name="qt">standard</str>
      <str name="q">solrpingquery</str>
      <str name="echoParams">all</str>
    </lst>
  </requestHandler>
  <requestHandler name="/debug/dump" class="solr.DumpRequestHandler" >
    <lst name="defaults">
     <str name="echoParams">explicit</str>
     <str name="echoHandler">true</str>
    </lst>
  </requestHandler>

  <queryResponseWriter name="standard" class="org.apache.solr.request.XMLResponseWriter"/>

    <highlighting>
        <fragmenter name="gap" class="org.apache.solr.highlight.GapFragmenter" default="true">
            <lst name="defaults">
                <int name="hl.fragsize">100</int>
            </lst>
        </fragmenter>
       <fragmenter name="regex" class="org.apache.solr.highlight.RegexFragmenter">
        <lst name="defaults">

          <int name="hl.fragsize">70</int>

          <float name="hl.regex.slop">0.5</float>

          <str name="hl.regex.pattern">[-\w ,/\n\"']{20,200}</str>
        </lst>
       </fragmenter>
       <formatter name="html" class="org.apache.solr.highlight.HtmlFormatter" default="true">
        <lst name="defaults">
         <str name="hl.simple.pre"><![CDATA[<em>]]></str>
         <str name="hl.simple.post"><![CDATA[</em>]]></str>
        </lst>
       </formatter>
  </highlighting>

    <admin>
        <defaultQuery>solr</defaultQuery>
    </admin>

    <requestHandler name="/replication" class="solr.ReplicationHandler" >
    ${master}
    ${slave}
    </requestHandler>

   <updateRequestProcessorChain name="" default="true" >
    <processor class="solr.LogUpdateProcessorFactory" >
      <int name="maxNumToLog">100</int>
    </processor>
    <processor class="solr.RunUpdateProcessorFactory" />
  </updateRequestProcessorChain>  
</config>

