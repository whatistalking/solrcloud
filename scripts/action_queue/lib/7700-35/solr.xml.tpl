<?xml version="1.0"?>
<!DOCTYPE Configure PUBLIC "-//Jetty//Configure//EN" "http://www.eclipse.org/jetty/configure.dtd">

<Configure id="Server" class="org.eclipse.jetty.server.Server">
    <Call name="addConnector">
        <Arg>
            <New class="org.eclipse.jetty.server.nio.SelectChannelConnector">
                <Set name="host">
                    <Property name="jetty.host" />
                </Set>
                <Set name="port">
                    <Property name="jetty.port" default="${jetty.port}" />
                </Set>
                <Set name="maxIdleTime">1000</Set>
                <Set name="Acceptors">2</Set>
                <Set name="statsOn">false</Set>
                <Set name="lowResourcesConnections">20000</Set>
                <Set name="lowResourcesMaxIdleTime">5000</Set>
            </New>
        </Arg>
    </Call>

    <Ref id="Handlers">
        <Call name="addHandler">
            <Arg>
                <New id="idx" class="org.eclipse.jetty.webapp.WebAppContext">
                    <Set name="contextPath">/solr</Set>
                    <Set name="war"><SystemProperty name="jetty.home" default="."/>/webapps/solr.war</Set>
                    <Set name="extractWAR">true</Set>
                    <Set name="copyWebDir">false</Set>
                    <Set name="defaultsDescriptor">
                        <SystemProperty name="jetty.home" default="." />/etc/webdefault.xml
                    </Set>
                    <Set name="configurationClasses"><Ref id="plusConfig"/></Set>

                    <New class="org.eclipse.jetty.plus.jndi.EnvEntry">
                        <Arg><Ref id="idx"/></Arg>
                        <Arg>solr/home</Arg>
                        <Arg type="java.lang.String">../idx</Arg>
                        <Arg type="java.lang.Boolean">true</Arg>
                    </New>

                </New>
            </Arg>
        </Call>
    </Ref>

</Configure>
