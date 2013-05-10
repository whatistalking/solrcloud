package com.anjuke.corp;

import java.io.UnsupportedEncodingException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import java.util.Collections;
import java.util.IdentityHashMap;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.Map.Entry;
import java.util.Map;

import org.apache.hadoop.hive.ql.exec.UDF;
import org.apache.hadoop.io.Text;

public class SolrSlowQuery extends UDF {
	
	public Text evaluate(Text text) {
		if(text == null) return new Text("");
		
		String str = text.toString();
		Pattern pattern = Pattern.compile("/([^/].*)/select[/]*\\?(.*)", Pattern.CASE_INSENSITIVE);
		Matcher matcher = pattern.matcher(str);
		
		String retstr = "";
		if (matcher.find()) {
			String service = matcher.group(1);
			String getparam = this.replace(matcher.group(2));
			retstr = getparam;
		}
		return new Text(retstr);
	}

	/**
	 * 替换，将url中参数替换
	 * */
	private String replace(String str){
		if (str != "") {
			Map<String, String> arrGet = this.parse_url(str);
			/*过滤ignore字段*/
			String[] ignore = {"wt","sort","version","fl","uid","act","pt","indent","hl"};
			Iterator<Entry<String, String>> it = arrGet.entrySet().iterator();
			while (it.hasNext()) {
			    Map.Entry entry = (Map.Entry) it.next();
			    if(this.in_array(entry.getKey().toString(), ignore)) it.remove();
			}
			/*替换*/
			int intTotal = 0;
			if(arrGet.get("start") != null) intTotal += Integer.parseInt(arrGet.get("start"));
			if(arrGet.get("rows") != null) intTotal += Integer.parseInt(arrGet.get("rows"));
			
			String[] rows = {"start","rows","facet_limit"};
			String[] q = {"q","fq","facet.query"};
			/**
			 * 想要一边遍历一边修改Map arrGet，
			 * 用for遍历，不能remove/add
			 * 用游标Iterator遍历，不能add
			 * 只能深度clone出来tmpMap，遍历tmpMap同时修改arrGet
			 * （浅度克隆：只是复制对象的引用，指向同一内存）
			 */
			Map<String,String> tmpMap = new IdentityHashMap<String, String>();
			tmpMap.putAll(arrGet);
			
			for(Map.Entry<String, String> entry: tmpMap.entrySet()){
				String k = entry.getKey().toString();
				String v = entry.getValue().toString();
				
				try {v = java.net.URLDecoder.decode(v, "UTF-8");}catch (Exception e) {}
				
				if(this.in_array(k, rows)){
					if(intTotal>1000) v = "M";
					else v = "N";
				}else if(this.in_array(k, q)){
					v = v.replaceAll("\\x22","\"");
					v = v.replaceAll("(\\w+)\\:\\s?\\[(\\s*\\d+(\\.\\d+)?\\s*) TO (\\s*\\d+(\\.\\d+)?\\s*)\\]","$1:[A TO B]");
					v = v.replaceAll("(\\w+)\\:\\s?\\[(\\s*\\*\\s*) TO (\\s*\\d+(\\.\\d+)?\\s*)\\]","$1:[A TO B]");
					v = v.replaceAll("(\\w+)\\:\\s?\\s?\\[(\\s*\\d+(\\.\\d+)?\\s*) TO (\\s*\\*\\s*)\\]","$1:[A TO B]");
					v = v.replaceAll("(\\w+)\\:\\s?[^\\[\\]\\s\\*\\,\\(\\)]+","$1:V");
					v = v.replaceAll("\\_val\\_:\\s?.*","_val_:FUNC");
					
					if(v.indexOf(":") == -1){
						v = this.explode_replace(" ", v, "V", " ");
					}
					if(v.indexOf("OR") > -1){
						v = this.explode_replace("OR", v, "V", " OR ");
					}
				}else if(k.indexOf("test")!=-1){
					arrGet.remove(k);
					k = "test_N";
				}else{
					v = v.replaceAll("\\d+(\\.\\d+)?","N");
				}
				arrGet.put(k, v);
			}
			/*整合url的参数*/
			return toQueryString(arrGet);
		}
		return "";
	}
	/**
	 * sort当key相同时按value排序 + http_build_query
	 */
	private String toQueryString(Map<String, String> map){

		ArrayList<KV> list = new ArrayList<KV>();
		for(Map.Entry<String, String> entry: map.entrySet()){
			String k = entry.getKey().toString();
			String v = entry.getValue().toString();
			list.add(new KV(k, v));
		}
		ComparatorUser comparator=new ComparatorUser();
		Collections.sort(list, comparator);
		StringBuffer queryString = new StringBuffer();
		
		for(KV item : list){
			queryString.append (item.getK() + "=" );
			String value = "";
	    	try {
	    		value = java.net.URLEncoder.encode ( item.getV(), "UTF-8" );
			} catch (UnsupportedEncodingException e) {}
	        queryString.append (value + "&" );
		}
	    if (queryString.length () > 0) {
	        queryString.deleteCharAt ( queryString.length () - 1 );
	    }
		return queryString.toString();
	}
	/**
	 * 将string按照delimiter切割，替换成replace和glue的字符串
	 */
	private String explode_replace(String delimiter, String string, String replace, String glue){
		String[] arr = string.split(delimiter);
		StringBuffer sb = new StringBuffer();
		for(int i=0;i<arr.length;i++){
			if(i==arr.length-1)
				sb.append(replace);
			else
				sb.append(replace+glue);
		}		
		return sb.toString();
	}
	/**
	 * in_array
	 */
	private boolean in_array(String k, String[] arr){
		int num = arr.length;
		for(int i=0;i<num;i++){
			if(k.equalsIgnoreCase(arr[i])) return true;
		}
		return false;
	}
	
	/**
	 * 切割url后缀参数
	 * */
	private Map<String, String> parse_url(String url){
		String[] queryStringSplit = url.split("&");
		Map<String, String> queryStringMap = new IdentityHashMap<String, String>();
		String[] queryStringParam;
        for (String qs : queryStringSplit) {
            queryStringParam = qs.split("=");
            if(queryStringParam.length < 2) 
            	continue;
            queryStringMap.put(queryStringParam[0].toLowerCase(), queryStringParam[1]);
        }
        return queryStringMap;
	}
}
