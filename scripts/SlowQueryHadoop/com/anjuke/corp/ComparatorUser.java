package com.anjuke.corp;
import java.util.Comparator;
public class ComparatorUser implements Comparator<Object> {
	public int compare(Object o1, Object o2) {
		KV a = (KV)o1;
		KV b = (KV)o2;
		int flag = a.getK().compareTo(b.getK());
		if(flag == 0) flag = a.getV().compareTo(b.getV());
		return flag;
	}
}
