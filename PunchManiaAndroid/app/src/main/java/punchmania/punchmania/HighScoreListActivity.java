package punchmania.punchmania;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.Toast;

import java.util.ArrayList;

import common.HighScoreList;

/**
 * Class is receiving the HardPunch highscorelist from server.
 * @author Benjamin Zakrisson
 */
public class HighScoreListActivity extends AppCompatActivity {

    private static final String TAG = "HighScoreListActivity";
    private ListView listView;
    private updater updater = new updater();
    private long clickedItemId;
    private ArrayList<String> convertedHighScoreListOld = new ArrayList<>();

    /**
     * Main method starts the other methods.
     * @param savedInstanceState
     */
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.highscorelist_layout);
        listView = (ListView) findViewById(R.id.highScoreListView);

        Log.d(TAG, "populateListView: Displaying data in the ListView.");
        //create the list adapter and set the adapter to the HighScore ArrayList

        updater.start();

        listView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                HighScoreList userToFetch = new HighScoreList();
                String userToFetchName = MainActivity.getHighScoresHard().getUser(position).getUser();
                int userToFetchScore = MainActivity.getHighScoresHard().getUser(position).getScore();
                userToFetch.add(userToFetchName, userToFetchScore);
                Log.i("HighScoreList: ", "Requesting details for " + userToFetch.getUser(0).getUser());
                MainActivity.staticSend(userToFetch, 7);
                Toast.makeText(HighScoreListActivity.this, MainActivity.getHighScoresHard().getUser(position).getUser(), Toast.LENGTH_LONG).show();
                boolean printed = false;
                while (!printed) {
                    if (MainActivity.getHighScoreDetails() != null && MainActivity.getHighScoreDetails().size() != 0) {
                        printed = true;
                        Intent intent = new Intent(HighScoreListActivity.this, OpenGLES20Activity.class);
                        startActivity(intent);
                    }
                }
            }
        });


    }

    /**
     * Creates an array list and coipies the content from the receiving list from server.
     * This array list is placed in a list adapter and shown in UI (highscorelist_layout).
     */
    private void populateListView() {
        Log.d(TAG, "populateListView: Displaying data in the ListView.");
        //create the list adapter and set the adapter to the HighScore ArrayList
        ArrayList<String> convertedHighScoreList = new ArrayList<>();
        for (int i = 0; i < MainActivity.getHighScoresHard().size(); i++) {
            convertedHighScoreList.add(i + 1 + ":   " + MainActivity.getHighScoresHard().getUser(i).getUser() + "    " + MainActivity.getHighScoresHard().getUser(i).getScore());
        }
        if (!convertedHighScoreListOld.toString().equals(convertedHighScoreList.toString())) {
            convertedHighScoreListOld = convertedHighScoreList;
            ListAdapter adapter = new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, convertedHighScoreList);
            listView.setAdapter(adapter);
        }
    }

    /**
     * Inner class running the method populateListView() every second to se if the list should be updated in UI.
     */
    public class updater extends Thread {
        @Override
        public void run() {
            while (!isInterrupted()) {
                try {
                    synchronized (this) {
                        wait(1000);
                        if (!isInterrupted())
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    populateListView();
                                }
                            });
                    }
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
        }
    }
}