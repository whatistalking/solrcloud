package com.anjuke.corp;
import org.apache.hadoop.hive.ql.exec.UDF;
import org.apache.hadoop.io.Text;

public class Rank extends UDF {
    private int  counter;
    private String last_key;
    public int evaluate(Text _key){
        String key = _key.toString();
        if ( !key.equalsIgnoreCase(this.last_key) ) {
            this.counter = 0;
            this.last_key = key;
        }
        return this.counter++;
    }
}

