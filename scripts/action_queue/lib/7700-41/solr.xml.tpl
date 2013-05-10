<?xml version="1.0"?>
<!DOCTYPE Configure PUBLIC "-//Jetty//Configure//EN" "http://www.eclipse.org/jetty/configure.dtd">

<!-- =============================================================== -->
<!-- Configure the Jetty Server                                      -->
<!--                                                                 -->
<!-- Documentation of this file format can be found at:              -->
<!-- http://wiki.eclipse.org/Jetty/Reference/jetty.xml_syntax        -->
<!--                                                                 -->
<!-- =============================================================== -->


<Configure id="Server" class="org.eclipse.jetty.server.Server">
    <Call name="addConnector">
      <Arg>
          <New class="org.eclipse.jetty.server.bio.SocketConnector">
            <Set name="host"><SystemProperty name="jetty.host" /></Set>
            <Set name="port"><SystemProperty name="jetty.port" default="${jetty.port}"/></Set>
            <Set name="maxIdleTime">50000</Set>
            <Set name="lowResourceMaxIdleTime">1500</Set>
            <Set name="statsOn">false</Set>
          </New>
      </Arg>
  </Call>

</Configure>
